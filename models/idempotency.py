from sqlalchemy import Column, String, Text, DateTime, Integer
from sqlalchemy.dialects.postgresql import UUID
from datetime import datetime, timedelta
import uuid
import os
from db import Base


class IdempotencyRecord(Base):
    """Store idempotency keys and responses for duplicate request detection"""
    
    __tablename__ = "idempotency_records"
    
    id = Column(UUID(as_uuid=True), primary_key=True, default=uuid.uuid4)
    user_id = Column(String(255), nullable=False, index=True)
    idempotency_key = Column(String(255), nullable=False, index=True)
    
    # Request context
    endpoint = Column(String(100), nullable=False)
    request_hash = Column(String(64), nullable=True)  # Hash of request body for additional validation
    
    # Response data
    response_data = Column(Text, nullable=False)  # JSON string of the response
    status_code = Column(Integer, nullable=False)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    expires_at = Column(DateTime, nullable=False)
    
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        if not self.expires_at:
            # Default TTL of 10 minutes
            ttl_seconds = int(os.getenv("IDEMPOTENCY_TTL_SECONDS", "600"))
            self.expires_at = datetime.utcnow() + timedelta(seconds=ttl_seconds)
    
    def to_dict(self):
        """Convert model to dictionary"""
        return {
            "id": str(self.id),
            "user_id": self.user_id,
            "idempotency_key": self.idempotency_key,
            "endpoint": self.endpoint,
            "request_hash": self.request_hash,
            "response_data": self.response_data,
            "status_code": self.status_code,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "expires_at": self.expires_at.isoformat() if self.expires_at else None,
        }
    
    @staticmethod
    def is_expired(record):
        """Check if idempotency record is expired"""
        return datetime.utcnow() > record.expires_at
