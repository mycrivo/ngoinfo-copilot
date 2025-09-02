from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, and_
from typing import Optional, Tuple, Dict, Any
from models.idempotency import IdempotencyRecord
from datetime import datetime
import json
import hashlib
import logging

logger = logging.getLogger(__name__)


class IdempotencyService:
    """Service for handling request idempotency"""

    def __init__(self, db_session: AsyncSession):
        self.db_session = db_session

    async def check_idempotency(
        self,
        user_id: str,
        idempotency_key: str,
        endpoint: str,
        request_data: Dict[str, Any] = None,
    ) -> Optional[Tuple[Dict[str, Any], int]]:
        """
        Check if request with this idempotency key exists
        Returns (response_data, status_code) if found, None if not found
        """
        try:
            # Create request hash for additional validation
            request_hash = None
            if request_data:
                request_str = json.dumps(request_data, sort_keys=True)
                request_hash = hashlib.sha256(request_str.encode()).hexdigest()

            # Look for existing record
            result = await self.db_session.execute(
                select(IdempotencyRecord).where(
                    and_(
                        IdempotencyRecord.user_id == user_id,
                        IdempotencyRecord.idempotency_key == idempotency_key,
                        IdempotencyRecord.endpoint == endpoint,
                        IdempotencyRecord.expires_at > datetime.utcnow(),
                    )
                )
            )
            record = result.scalar_one_or_none()

            if record:
                # Verify request hash matches (if provided)
                if request_hash and record.request_hash != request_hash:
                    logger.warning(
                        f"Idempotency key conflict: same key, different request for user {user_id}"
                    )
                    # Return None to force new processing - this indicates a conflict
                    return None

                # Return cached response
                response_data = json.loads(record.response_data)
                logger.info(
                    f"Returning cached response for idempotency key: {idempotency_key}"
                )
                return response_data, record.status_code

            return None

        except Exception as e:
            logger.error(f"Error checking idempotency: {str(e)}")
            # On error, allow the request to proceed
            return None

    async def store_response(
        self,
        user_id: str,
        idempotency_key: str,
        endpoint: str,
        response_data: Dict[str, Any],
        status_code: int,
        request_data: Dict[str, Any] = None,
    ) -> bool:
        """Store response for idempotency"""
        try:
            # Create request hash
            request_hash = None
            if request_data:
                request_str = json.dumps(request_data, sort_keys=True)
                request_hash = hashlib.sha256(request_str.encode()).hexdigest()

            # Store the record
            record = IdempotencyRecord(
                user_id=user_id,
                idempotency_key=idempotency_key,
                endpoint=endpoint,
                request_hash=request_hash,
                response_data=json.dumps(response_data),
                status_code=status_code,
            )

            self.db_session.add(record)
            await self.db_session.commit()

            logger.info(f"Stored idempotency record for key: {idempotency_key}")
            return True

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error storing idempotency record: {str(e)}")
            return False

    async def cleanup_expired(self) -> int:
        """Clean up expired idempotency records"""
        try:
            from sqlalchemy import delete

            result = await self.db_session.execute(
                delete(IdempotencyRecord).where(
                    IdempotencyRecord.expires_at <= datetime.utcnow()
                )
            )

            deleted_count = result.rowcount
            await self.db_session.commit()

            if deleted_count > 0:
                logger.info(f"Cleaned up {deleted_count} expired idempotency records")

            return deleted_count

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error cleaning up expired idempotency records: {str(e)}")
            return 0
