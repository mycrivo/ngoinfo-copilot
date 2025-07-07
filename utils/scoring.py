from typing import Dict, Any, Optional
from models.ngo_profiles import NGOProfile
from models.funding_opportunities import FundingOpportunity
import logging

logger = logging.getLogger(__name__)


def calculate_profile_completeness(profile_data: Dict[str, Any]) -> int:
    """
    Calculate profile completeness score (0-100)
    
    Args:
        profile_data: Dictionary containing profile information
        
    Returns:
        int: Completeness score from 0 to 100
    """
    try:
        score = 0
        total_fields = 0
        
        # Required fields (higher weight)
        required_fields = {
            "organization_name": 10,
            "mission_statement": 10,
            "focus_areas": 8,
            "geographic_scope": 8,
        }
        
        for field, weight in required_fields.items():
            total_fields += weight
            if profile_data.get(field):
                if isinstance(profile_data[field], list):
                    if len(profile_data[field]) > 0:
                        score += weight
                else:
                    score += weight
        
        # Important fields (medium weight)
        important_fields = {
            "founded_year": 5,
            "organization_type": 5,
            "website": 5,
            "contact_person": 5,
            "contact_email": 5,
            "programs_services": 6,
            "target_beneficiaries": 6,
            "annual_budget_range": 4,
            "staff_size": 4,
        }
        
        for field, weight in important_fields.items():
            total_fields += weight
            if profile_data.get(field):
                if isinstance(profile_data[field], list):
                    if len(profile_data[field]) > 0:
                        score += weight
                else:
                    score += weight
        
        # Optional fields (lower weight)
        optional_fields = {
            "registration_number": 2,
            "contact_phone": 2,
            "address": 2,
            "past_projects": 3,
            "partnerships": 3,
            "awards_recognition": 2,
            "funding_sources": 3,
            "grant_experience": 3,
        }
        
        for field, weight in optional_fields.items():
            total_fields += weight
            if profile_data.get(field):
                if isinstance(profile_data[field], list):
                    if len(profile_data[field]) > 0:
                        score += weight
                else:
                    score += weight
        
        # Calculate percentage
        completeness_percentage = int((score / total_fields) * 100) if total_fields > 0 else 0
        
        logger.debug(f"Profile completeness calculated: {completeness_percentage}%")
        return completeness_percentage
        
    except Exception as e:
        logger.error(f"Error calculating profile completeness: {str(e)}")
        return 0


def calculate_proposal_scores(
    proposal_content: str,
    funding_opportunity: FundingOpportunity,
    ngo_profile: NGOProfile
) -> Dict[str, Optional[float]]:
    """
    Calculate quality scores for a generated proposal
    
    Args:
        proposal_content: The generated proposal text
        funding_opportunity: The funding opportunity object
        ngo_profile: The NGO profile object
        
    Returns:
        Dict containing confidence_score, alignment_score, and completeness_score
    """
    try:
        scores = {
            "confidence_score": None,
            "alignment_score": None,
            "completeness_score": None
        }
        
        # Calculate confidence score based on content quality
        scores["confidence_score"] = _calculate_confidence_score(proposal_content)
        
        # Calculate alignment score based on funding opportunity match
        scores["alignment_score"] = _calculate_alignment_score(
            proposal_content, funding_opportunity, ngo_profile
        )
        
        # Calculate completeness score based on content structure
        scores["completeness_score"] = _calculate_completeness_score(proposal_content)
        
        logger.debug(f"Proposal scores calculated: {scores}")
        return scores
        
    except Exception as e:
        logger.error(f"Error calculating proposal scores: {str(e)}")
        return {"confidence_score": None, "alignment_score": None, "completeness_score": None}


def _calculate_confidence_score(proposal_content: str) -> float:
    """Calculate confidence score based on content quality indicators"""
    try:
        score = 0.0
        
        # Length indicators
        word_count = len(proposal_content.split())
        if word_count >= 500:
            score += 0.2
        elif word_count >= 300:
            score += 0.15
        elif word_count >= 200:
            score += 0.1
        
        # Structure indicators
        if "executive summary" in proposal_content.lower():
            score += 0.15
        if "budget" in proposal_content.lower():
            score += 0.1
        if "methodology" in proposal_content.lower():
            score += 0.1
        if "timeline" in proposal_content.lower():
            score += 0.1
        if "impact" in proposal_content.lower():
            score += 0.1
        
        # Quality indicators
        sentences = proposal_content.split('.')
        if len(sentences) >= 10:
            score += 0.1
        
        # Ensure score is between 0 and 1
        return min(max(score, 0.0), 1.0)
        
    except Exception as e:
        logger.error(f"Error calculating confidence score: {str(e)}")
        return 0.5


def _calculate_alignment_score(
    proposal_content: str,
    funding_opportunity: FundingOpportunity,
    ngo_profile: NGOProfile
) -> float:
    """Calculate alignment score based on funding opportunity match"""
    try:
        score = 0.0
        content_lower = proposal_content.lower()
        
        # Check focus area alignment
        if funding_opportunity.focus_areas:
            for focus_area in funding_opportunity.focus_areas:
                if focus_area.lower() in content_lower:
                    score += 0.2
                    break
        
        # Check organization type alignment
        if funding_opportunity.organization_types:
            if ngo_profile.organization_type:
                for org_type in funding_opportunity.organization_types:
                    if org_type.lower() in ngo_profile.organization_type.lower():
                        score += 0.15
                        break
        
        # Check geographic alignment
        if funding_opportunity.geographic_focus and ngo_profile.geographic_scope:
            for geo_area in funding_opportunity.geographic_focus:
                if any(geo_area.lower() in scope.lower() for scope in ngo_profile.geographic_scope):
                    score += 0.15
                    break
        
        # Check keyword alignment
        if funding_opportunity.keywords:
            keyword_matches = sum(1 for keyword in funding_opportunity.keywords 
                                if keyword.lower() in content_lower)
            if keyword_matches > 0:
                score += min(keyword_matches * 0.1, 0.3)
        
        # Check donor organization mention
        if funding_opportunity.donor_organization:
            if funding_opportunity.donor_organization.lower() in content_lower:
                score += 0.1
        
        # Ensure score is between 0 and 1
        return min(max(score, 0.0), 1.0)
        
    except Exception as e:
        logger.error(f"Error calculating alignment score: {str(e)}")
        return 0.5


def _calculate_completeness_score(proposal_content: str) -> float:
    """Calculate completeness score based on content structure"""
    try:
        score = 0.0
        content_lower = proposal_content.lower()
        
        # Check for key sections
        key_sections = [
            "executive summary",
            "problem statement",
            "objectives",
            "methodology",
            "budget",
            "timeline",
            "impact",
            "sustainability",
            "evaluation",
            "conclusion"
        ]
        
        sections_found = sum(1 for section in key_sections if section in content_lower)
        score += (sections_found / len(key_sections)) * 0.6
        
        # Check for financial information
        financial_keywords = ["budget", "cost", "funding", "expense", "financial"]
        if any(keyword in content_lower for keyword in financial_keywords):
            score += 0.15
        
        # Check for measurable outcomes
        outcome_keywords = ["target", "goal", "outcome", "result", "metric", "indicator"]
        if any(keyword in content_lower for keyword in outcome_keywords):
            score += 0.15
        
        # Check for organization details
        if "organization" in content_lower or "ngo" in content_lower:
            score += 0.1
        
        # Ensure score is between 0 and 1
        return min(max(score, 0.0), 1.0)
        
    except Exception as e:
        logger.error(f"Error calculating completeness score: {str(e)}")
        return 0.5 