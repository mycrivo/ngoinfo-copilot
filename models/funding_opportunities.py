from sqlalchemy import Column, String, Text, DateTime, JSON, Boolean, Integer, Float
from datetime import datetime
from db import Base


class FundingOpportunity(Base):
    """
    Read-only model for funding opportunities from ReqAgent
    This mirrors the structure of the funding_opportunities table
    """
    
    __tablename__ = "funding_opportunities"
    
    # Primary key
    id = Column(Integer, primary_key=True)
    
    # Basic Information
    title = Column(String(500), nullable=False)
    description = Column(Text, nullable=True)
    donor_organization = Column(String(255), nullable=True)
    funding_type = Column(String(100), nullable=True)  # Grant, Fellowship, etc.
    
    # Funding Details
    amount_min = Column(Float, nullable=True)
    amount_max = Column(Float, nullable=True)
    currency = Column(String(10), nullable=True)
    total_funding_available = Column(Float, nullable=True)
    
    # Eligibility & Requirements
    eligibility_criteria = Column(JSON, nullable=True)  # Structured eligibility requirements
    geographic_focus = Column(JSON, nullable=True)  # Countries/regions eligible
    focus_areas = Column(JSON, nullable=True)  # Thematic areas
    organization_types = Column(JSON, nullable=True)  # Types of orgs eligible
    
    # Timeline
    application_deadline = Column(DateTime, nullable=True)
    funding_start_date = Column(DateTime, nullable=True)
    funding_end_date = Column(DateTime, nullable=True)
    
    # Application Process
    application_process = Column(Text, nullable=True)
    required_documents = Column(JSON, nullable=True)  # List of required docs
    application_url = Column(String(1000), nullable=True)
    contact_information = Column(JSON, nullable=True)
    
    # Structured Data (from ReqAgent parsing)
    structured_requirements = Column(JSON, nullable=True)  # Parsed requirements
    keywords = Column(JSON, nullable=True)  # Extracted keywords
    priority_score = Column(Float, nullable=True)  # ReqAgent's priority scoring
    
    # Source Information
    source_url = Column(String(1000), nullable=True)
    source_type = Column(String(100), nullable=True)  # Website, PDF, etc.
    
    # Processing Metadata (from ReqAgent)
    processing_status = Column(String(50), nullable=True)
    parsing_confidence = Column(Float, nullable=True)
    last_verified = Column(DateTime, nullable=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    
    # Status flags
    is_active = Column(Boolean, default=True, nullable=False)
    is_archived = Column(Boolean, default=False, nullable=False)
    
    def to_dict(self):
        """Convert model to dictionary"""
        return {
            "id": self.id,
            "title": self.title,
            "description": self.description,
            "donor_organization": self.donor_organization,
            "funding_type": self.funding_type,
            "amount_min": self.amount_min,
            "amount_max": self.amount_max,
            "currency": self.currency,
            "total_funding_available": self.total_funding_available,
            "eligibility_criteria": self.eligibility_criteria,
            "geographic_focus": self.geographic_focus,
            "focus_areas": self.focus_areas,
            "organization_types": self.organization_types,
            "application_deadline": self.application_deadline.isoformat() if self.application_deadline else None,
            "funding_start_date": self.funding_start_date.isoformat() if self.funding_start_date else None,
            "funding_end_date": self.funding_end_date.isoformat() if self.funding_end_date else None,
            "application_process": self.application_process,
            "required_documents": self.required_documents,
            "application_url": self.application_url,
            "contact_information": self.contact_information,
            "structured_requirements": self.structured_requirements,
            "keywords": self.keywords,
            "priority_score": self.priority_score,
            "source_url": self.source_url,
            "source_type": self.source_type,
            "processing_status": self.processing_status,
            "parsing_confidence": self.parsing_confidence,
            "last_verified": self.last_verified.isoformat() if self.last_verified else None,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "updated_at": self.updated_at.isoformat() if self.updated_at else None,
            "is_active": self.is_active,
            "is_archived": self.is_archived,
        }
    
    def to_summary_dict(self):
        """Convert model to summary dictionary (for lists)"""
        return {
            "id": self.id,
            "title": self.title,
            "donor_organization": self.donor_organization,
            "funding_type": self.funding_type,
            "amount_min": self.amount_min,
            "amount_max": self.amount_max,
            "currency": self.currency,
            "application_deadline": self.application_deadline.isoformat() if self.application_deadline else None,
            "focus_areas": self.focus_areas,
            "priority_score": self.priority_score,
        } 