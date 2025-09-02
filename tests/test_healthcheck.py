import pytest
from unittest.mock import AsyncMock, patch
from fastapi.testclient import TestClient
from main import app


class TestHealthcheckEndpoint:
    """Test cases for the healthcheck endpoint."""

    @pytest.fixture
    def client(self):
        """Create a test client."""
        return TestClient(app)

    @patch("db.engine")
    def test_healthcheck_success(self, mock_engine, client):
        """Test successful healthcheck with database up."""
        # Mock successful database connection
        mock_conn = AsyncMock()
        mock_conn.execute.return_value.scalar_one.return_value = 1
        mock_engine.connect.return_value.__aenter__.return_value = mock_conn

        response = client.get("/healthcheck")

        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "ok"
        assert data["service"] == "NGOInfo-Copilot"
        assert data["version"] == "1.0.0"
        assert data["db"] == "up"
        assert "timestamp" in data
        assert "db_error" not in data

    @patch("db.engine")
    def test_healthcheck_database_down(self, mock_engine, client):
        """Test healthcheck when database is down."""
        # Mock database connection failure
        mock_engine.connect.side_effect = Exception("Connection failed")

        response = client.get("/healthcheck")

        assert response.status_code == 503
        data = response.json()
        assert data["status"] == "degraded"
        assert data["db"] == "down"
        assert "db_error" in data
        assert "Connection failed" in data["db_error"]

    @patch("db.engine")
    def test_healthcheck_database_timeout(self, mock_engine, client):
        """Test healthcheck with database timeout."""
        # Mock database timeout
        mock_engine.connect.side_effect = TimeoutError("Database timeout")

        response = client.get("/healthcheck")

        assert response.status_code == 503
        data = response.json()
        assert data["status"] == "degraded"
        assert data["db"] == "down"
        assert "db_error" in data
        assert "TimeoutError" in data["db_error"]

    def test_healthcheck_response_structure(self, client):
        """Test that healthcheck response has the correct structure."""
        response = client.get("/healthcheck")

        assert response.status_code in [200, 503]
        data = response.json()

        # Check required fields
        required_fields = ["status", "service", "version", "timestamp", "db"]
        for field in required_fields:
            assert field in data

        # Check field types
        assert isinstance(data["status"], str)
        assert isinstance(data["service"], str)
        assert isinstance(data["version"], str)
        assert isinstance(data["timestamp"], str)
        assert isinstance(data["db"], str)

        # Check valid values
        assert data["status"] in ["ok", "degraded"]
        assert data["db"] in ["up", "down"]
        assert data["service"] == "NGOInfo-Copilot"
        assert data["version"] == "1.0.0"
