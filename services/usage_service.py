from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func
from typing import Dict, Any
from models.usage import UsageLedger
from datetime import datetime
import logging

logger = logging.getLogger(__name__)


class UsageService:
    """Service for tracking and querying API usage"""

    def __init__(self, db_session: AsyncSession):
        self.db_session = db_session

    async def get_usage_summary(self, user_id: str) -> Dict[str, Any]:
        """Get current month usage summary for user"""
        try:
            month_start = UsageLedger.get_current_month_start()
            next_month = UsageLedger.get_next_month_start()

            # Get user's current plan info (from most recent entry or default)
            latest_entry_result = await self.db_session.execute(
                select(UsageLedger)
                .where(UsageLedger.user_id == user_id)
                .order_by(UsageLedger.created_at.desc())
                .limit(1)
            )
            latest_entry = latest_entry_result.scalar_one_or_none()

            # Default plan info
            plan_name = "free"
            monthly_limit = 10

            if latest_entry:
                plan_name = latest_entry.plan_name
                monthly_limit = latest_entry.monthly_limit

            # Calculate usage for current month
            usage_result = await self.db_session.execute(
                select(func.coalesce(func.sum(UsageLedger.count), 0)).where(
                    UsageLedger.user_id == user_id,
                    UsageLedger.created_at >= month_start,
                    UsageLedger.created_at < next_month,
                )
            )
            used = usage_result.scalar() or 0

            remaining = max(0, monthly_limit - used)

            return {
                "plan": plan_name,
                "monthly_limit": monthly_limit,
                "used": used,
                "remaining": remaining,
                "reset_at": next_month.isoformat(),
            }

        except Exception as e:
            logger.error(f"Error getting usage summary for user {user_id}: {str(e)}")
            # Return safe defaults on error
            return {
                "plan": "free",
                "monthly_limit": 10,
                "used": 0,
                "remaining": 10,
                "reset_at": UsageLedger.get_next_month_start().isoformat(),
            }

    async def record_usage(
        self,
        user_id: str,
        action_type: str,
        count: int = 1,
        plan_name: str = "free",
        monthly_limit: int = 10,
    ) -> bool:
        """Record API usage for billing/limits"""
        try:
            usage_entry = UsageLedger(
                user_id=user_id,
                action_type=action_type,
                count=count,
                plan_name=plan_name,
                monthly_limit=monthly_limit,
            )

            self.db_session.add(usage_entry)
            await self.db_session.commit()

            logger.info(
                f"Recorded usage: user={user_id}, action={action_type}, count={count}"
            )
            return True

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error recording usage for user {user_id}: {str(e)}")
            return False

    async def check_rate_limit(
        self, user_id: str, action_type: str, limit_per_minute: int
    ) -> bool:
        """Check if user is within rate limit for specific action"""
        try:
            # Check usage in the last minute
            one_minute_ago = datetime.utcnow().replace(second=0, microsecond=0)

            usage_result = await self.db_session.execute(
                select(func.coalesce(func.sum(UsageLedger.count), 0)).where(
                    UsageLedger.user_id == user_id,
                    UsageLedger.action_type == action_type,
                    UsageLedger.created_at >= one_minute_ago,
                )
            )
            current_usage = usage_result.scalar() or 0

            return current_usage < limit_per_minute

        except Exception as e:
            logger.error(f"Error checking rate limit for user {user_id}: {str(e)}")
            # Allow on error to avoid blocking legitimate requests
            return True
