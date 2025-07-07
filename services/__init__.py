"""
Service layer for NGOInfo-Copilot
"""

from .profile_service import ProfileService
from .proposal_service import ProposalService
from .ngo_profile_manager import NGOProfileManager

__all__ = ["ProfileService", "ProposalService", "NGOProfileManager"] 