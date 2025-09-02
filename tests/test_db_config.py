import pytest
import os
from unittest.mock import patch
from utils.db_config import resolve_database_url, normalize_database_url


class TestDatabaseURLResolver:
    """Test cases for database URL resolution and normalization."""

    def test_resolve_database_url_priority_order(self):
        """Test that environment variables are checked in correct priority order."""
        env_vars = {
            "DATABASE_URL": "",
            "COPILOT_DATABASE_URL": "postgresql://test2:5432/db",
            "POSTGRES_URL": "postgresql://test3:5432/db",
            "DATABASE_CONNECTION_STRING": "postgresql://test4:5432/db",
        }

        with patch.dict(os.environ, env_vars):
            url = resolve_database_url()
            assert "test2" in url  # Should use COPILOT_DATABASE_URL

    def test_resolve_database_url_fallback(self):
        """Test fallback to lower priority environment variables."""
        env_vars = {
            "DATABASE_URL": "",
            "COPILOT_DATABASE_URL": "",
            "POSTGRES_URL": "postgresql://test3:5432/db",
            "DATABASE_CONNECTION_STRING": "postgresql://test4:5432/db",
        }

        with patch.dict(os.environ, env_vars):
            url = resolve_database_url()
            assert "test3" in url  # Should use POSTGRES_URL

    def test_resolve_database_url_no_vars(self):
        """Test error when no environment variables are set."""
        with patch.dict(os.environ, {}, clear=True):
            with pytest.raises(
                ValueError, match="None of the following environment variables"
            ):
                resolve_database_url()

    def test_normalize_database_url_postgres_to_psycopg2(self):
        """Test conversion of postgres:// to postgresql+psycopg2://."""
        url = "postgres://user:pass@host:5432/db"
        normalized = normalize_database_url(url)
        assert normalized.startswith("postgresql+psycopg2://")
        assert "sslmode=require" in normalized

    def test_normalize_database_url_postgresql_to_psycopg2(self):
        """Test conversion of postgresql:// to postgresql+psycopg2://."""
        url = "postgresql://user:pass@host:5432/db"
        normalized = normalize_database_url(url)
        assert normalized.startswith("postgresql+psycopg2://")
        assert "sslmode=require" in normalized

    def test_normalize_database_url_keep_asyncpg(self):
        """Test that postgresql+asyncpg:// is preserved."""
        url = "postgresql+asyncpg://user:pass@host:5432/db"
        normalized = normalize_database_url(url)
        assert normalized.startswith("postgresql+asyncpg://")
        assert "sslmode=require" in normalized

    def test_normalize_database_url_add_sslmode(self):
        """Test adding sslmode=require when not present."""
        url = "postgresql://user:pass@host:5432/db"
        normalized = normalize_database_url(url)
        assert "sslmode=require" in normalized

    def test_normalize_database_url_preserve_existing_sslmode(self):
        """Test preserving existing sslmode parameter."""
        url = "postgresql://user:pass@host:5432/db?sslmode=prefer"
        normalized = normalize_database_url(url)
        assert "sslmode=prefer" in normalized
        assert normalized.count("sslmode") == 1

    def test_normalize_database_url_with_query_params(self):
        """Test handling of existing query parameters."""
        url = "postgresql://user:pass@host:5432/db?connect_timeout=10"
        normalized = normalize_database_url(url)
        assert "connect_timeout=10" in normalized
        assert "sslmode=require" in normalized
        assert normalized.count("?") == 1
        assert "&" in normalized

    def test_normalize_database_url_with_fragment(self):
        """Test preserving URL fragment."""
        url = "postgresql://user:pass@host:5432/db#fragment"
        normalized = normalize_database_url(url)
        assert normalized.endswith("#fragment")
        assert "sslmode=require" in normalized
