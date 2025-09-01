from sqlalchemy import Column, String, Integer, DateTime, Boolean
from sqlalchemy.dialects.postgresql import UUID
from datetime import datetime, timezone, timedelta
import uuid
from db import Base


class UsageLedger(Base):
    """Usage tracking for API limits and billing"""
    
    __tablename__ = "usage_ledger"
    
    id = Column(UUID(as_uuid=True), primary_key=True, default=uuid.uuid4)
    user_id = Column(String(255), nullable=False, index=True)
    
    # Usage tracking
    action_type = Column(String(50), nullable=False)  # 'generate', 'export', etc.
    count = Column(Integer, default=1, nullable=False)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    
    # Plan information (cached for quick lookup)
    plan_name = Column(String(100), default="free", nullable=False)
    monthly_limit = Column(Integer, default=10, nullable=False)
    
    def to_dict(self):
        """Convert model to dictionary"""
        return {
            "id": str(self.id),
            "user_id": self.user_id,
            "action_type": self.action_type,
            "count": self.count,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "plan_name": self.plan_name,
            "monthly_limit": self.monthly_limit,
        }
    
    @staticmethod
    def get_current_month_start():
        """Get start of current month in UTC"""
        now = datetime.now(timezone.utc)
        return now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
    
    @staticmethod
    def get_next_month_start():
        """Get start of next month in UTC"""
        current_start = UsageLedger.get_current_month_start()
        # Add approximately one month (handle month boundaries)
        if current_start.month == 12:
            return current_start.replace(year=current_start.year + 1, month=1)
        else:
            return current_start.replace(month=current_start.month + 1)
