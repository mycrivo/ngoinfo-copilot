from sqlalchemy import (
    Column,
    String,
    Text,
    DateTime,
    JSON,
    Boolean,
    Integer,
    ForeignKey,
    Float,
)
from sqlalchemy.dialects.postgresql import UUID
from sqlalchemy.orm import relationship
from datetime import datetime
import uuid
from db import Base


class Proposal(Base):
    """Proposal model for storing generated proposals"""

    __tablename__ = "proposals"

    id = Column(UUID(as_uuid=True), primary_key=True, default=uuid.uuid4)
    user_id = Column(String(255), nullable=False)  # External user identifier
    ngo_profile_id = Column(
        UUID(as_uuid=True), ForeignKey("ngo_profiles.id"), nullable=False
    )
    funding_opportunity_id = Column(
        Integer, nullable=False
    )  # References ReqAgent's funding_opportunities

    # Proposal Content
    title = Column(String(500), nullable=False)
    content = Column(Text, nullable=False)  # Full proposal text
    executive_summary = Column(Text, nullable=True)

    # Generation Metadata
    generation_prompt = Column(Text, nullable=False)  # Prompt used for generation
    donor_template_used = Column(String(255), nullable=True)  # Template identifier
    ai_model_used = Column(String(100), nullable=False)  # e.g., "gpt-4"
    generation_timestamp = Column(DateTime, default=datetime.utcnow, nullable=False)

    # Quality Metrics
    confidence_score = Column(Float, nullable=True)  # 0.0-1.0 confidence
    alignment_score = Column(
        Float, nullable=True
    )  # How well it matches funding criteria
    completeness_score = Column(Float, nullable=True)  # How complete the proposal is

    # User Interactions
    user_rating = Column(Integer, nullable=True)  # 1-5 star rating
    user_feedback = Column(Text, nullable=True)  # User comments
    edit_history = Column(JSON, nullable=True)  # Track user edits

    # Status & Workflow
    status = Column(
        String(50), default="draft", nullable=False
    )  # draft, reviewed, finalized, submitted
    version = Column(Integer, default=1, nullable=False)  # Version number
    parent_proposal_id = Column(
        UUID(as_uuid=True), ForeignKey("proposals.id"), nullable=True
    )  # For revisions

    # Export Information
    exported_formats = Column(
        JSON, nullable=True
    )  # List of formats exported (pdf, docx, etc.)
    export_count = Column(Integer, default=0, nullable=False)
    last_exported_at = Column(DateTime, nullable=True)

    # Funding Opportunity Snapshot (for reference)
    funding_opportunity_snapshot = Column(
        JSON, nullable=True
    )  # Snapshot of funding opp at generation time

    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False
    )

    # Status flags
    is_active = Column(Boolean, default=True, nullable=False)
    is_archived = Column(Boolean, default=False, nullable=False)

    def to_dict(self):
        """Convert model to dictionary"""
        return {
            "id": str(self.id),
            "user_id": self.user_id,
            "ngo_profile_id": str(self.ngo_profile_id),
            "funding_opportunity_id": self.funding_opportunity_id,
            "title": self.title,
            "content": self.content,
            "executive_summary": self.executive_summary,
            "generation_prompt": self.generation_prompt,
            "donor_template_used": self.donor_template_used,
            "ai_model_used": self.ai_model_used,
            "generation_timestamp": (
                self.generation_timestamp.isoformat()
                if self.generation_timestamp
                else None
            ),
            "confidence_score": self.confidence_score,
            "alignment_score": self.alignment_score,
            "completeness_score": self.completeness_score,
            "user_rating": self.user_rating,
            "user_feedback": self.user_feedback,
            "edit_history": self.edit_history,
            "status": self.status,
            "version": self.version,
            "parent_proposal_id": (
                str(self.parent_proposal_id) if self.parent_proposal_id else None
            ),
            "exported_formats": self.exported_formats,
            "export_count": self.export_count,
            "last_exported_at": (
                self.last_exported_at.isoformat() if self.last_exported_at else None
            ),
            "funding_opportunity_snapshot": self.funding_opportunity_snapshot,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "updated_at": self.updated_at.isoformat() if self.updated_at else None,
            "is_active": self.is_active,
            "is_archived": self.is_archived,
        }

    def to_summary_dict(self):
        """Convert model to summary dictionary (for lists)"""
        return {
            "id": str(self.id),
            "title": self.title,
            "status": self.status,
            "confidence_score": self.confidence_score,
            "user_rating": self.user_rating,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "updated_at": self.updated_at.isoformat() if self.updated_at else None,
        }
