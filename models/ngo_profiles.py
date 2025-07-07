from sqlalchemy import Column, String, Text, DateTime, JSON, Boolean, Integer
from sqlalchemy.dialects.postgresql import UUID
from datetime import datetime
import uuid
from db import Base


class NGOProfile(Base):
    """NGO Profile model for storing organization information"""
    
    __tablename__ = "ngo_profiles"
    
    id = Column(UUID(as_uuid=True), primary_key=True, default=uuid.uuid4)
    user_id = Column(String(255), nullable=False, unique=True)  # External user identifier
    
    # Basic Organization Info
    organization_name = Column(String(500), nullable=False)
    mission_statement = Column(Text, nullable=False)
    focus_areas = Column(JSON, nullable=False)  # List of focus areas
    geographic_scope = Column(JSON, nullable=False)  # Countries/regions served
    
    # Organizational Details
    founded_year = Column(Integer, nullable=True)
    organization_type = Column(String(100), nullable=True)  # Non-profit, NGO, etc.
    registration_number = Column(String(100), nullable=True)
    website = Column(String(500), nullable=True)
    
    # Contact Information
    contact_person = Column(String(255), nullable=True)
    contact_email = Column(String(255), nullable=True)
    contact_phone = Column(String(50), nullable=True)
    address = Column(Text, nullable=True)
    
    # Program Information
    programs_services = Column(JSON, nullable=True)  # List of programs/services
    target_beneficiaries = Column(JSON, nullable=True)  # Demographics served
    annual_budget_range = Column(String(100), nullable=True)
    staff_size = Column(String(100), nullable=True)
    
    # Experience & Achievements
    past_projects = Column(JSON, nullable=True)  # List of significant projects
    partnerships = Column(JSON, nullable=True)  # Key partnerships
    awards_recognition = Column(JSON, nullable=True)  # Awards received
    
    # Funding History
    funding_sources = Column(JSON, nullable=True)  # Types of funding received
    grant_experience = Column(JSON, nullable=True)  # Past grant experiences
    
    # Profile Metadata
    profile_completeness_score = Column(Integer, default=0)  # 0-100 score
    ai_optimization_notes = Column(Text, nullable=True)  # AI-generated profile insights
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    
    # Status flags
    is_active = Column(Boolean, default=True, nullable=False)
    is_verified = Column(Boolean, default=False, nullable=False)
    
    def to_dict(self):
        """Convert model to dictionary"""
        return {
            "id": str(self.id),
            "user_id": self.user_id,
            "organization_name": self.organization_name,
            "mission_statement": self.mission_statement,
            "focus_areas": self.focus_areas,
            "geographic_scope": self.geographic_scope,
            "founded_year": self.founded_year,
            "organization_type": self.organization_type,
            "registration_number": self.registration_number,
            "website": self.website,
            "contact_person": self.contact_person,
            "contact_email": self.contact_email,
            "contact_phone": self.contact_phone,
            "address": self.address,
            "programs_services": self.programs_services,
            "target_beneficiaries": self.target_beneficiaries,
            "annual_budget_range": self.annual_budget_range,
            "staff_size": self.staff_size,
            "past_projects": self.past_projects,
            "partnerships": self.partnerships,
            "awards_recognition": self.awards_recognition,
            "funding_sources": self.funding_sources,
            "grant_experience": self.grant_experience,
            "profile_completeness_score": self.profile_completeness_score,
            "ai_optimization_notes": self.ai_optimization_notes,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "updated_at": self.updated_at.isoformat() if self.updated_at else None,
            "is_active": self.is_active,
            "is_verified": self.is_verified,
        } 