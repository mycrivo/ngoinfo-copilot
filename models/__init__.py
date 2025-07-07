"""
Database models for NGOInfo-Copilot
"""

from .ngo_profiles import NGOProfile
from .proposals import Proposal
from .funding_opportunities import FundingOpportunity
from .users import User

__all__ = [
    "NGOProfile",
    "Proposal", 
    "FundingOpportunity",
    "User"
] 