from typing import Optional, Dict, Any
from models.ngo_profiles import NGOProfile
from models.funding_opportunities import FundingOpportunity
from .donor_templates import DonorTemplates
import logging

logger = logging.getLogger(__name__)


class PromptBuilder:
    """Build AI prompts for proposal generation"""
    
    def __init__(self):
        self.donor_templates = DonorTemplates()
    
    def build_proposal_prompt(
        self,
        profile: NGOProfile,
        funding_opportunity: FundingOpportunity,
        custom_instructions: Optional[str] = None
    ) -> str:
        """
        Build a comprehensive prompt for proposal generation
        
        Args:
            profile: NGO profile containing organization information
            funding_opportunity: Funding opportunity details
            custom_instructions: Optional custom instructions from user
            
        Returns:
            str: Complete prompt for AI generation
        """
        try:
            # Get donor-specific template
            donor_template = self.donor_templates.get_template(
                funding_opportunity.donor_organization
            )
            
            # Build the prompt
            prompt = f"""
You are an expert grant writer specializing in NGO proposals. Generate a comprehensive, professional proposal that aligns with the funding requirements and showcases the organization's capabilities.

ORGANIZATION PROFILE:
{self._format_organization_profile(profile)}

FUNDING OPPORTUNITY:
{self._format_funding_opportunity(funding_opportunity)}

DONOR-SPECIFIC GUIDELINES:
{donor_template}

PROPOSAL REQUIREMENTS:
1. Create a compelling proposal that directly addresses the funding opportunity
2. Highlight the organization's relevant experience and qualifications
3. Demonstrate clear alignment with the donor's priorities and focus areas
4. Include specific, measurable outcomes and impact metrics
5. Present a realistic budget and timeline
6. Use professional, persuasive language appropriate for grant applications
7. Ensure the proposal is well-structured with clear sections

REQUIRED SECTIONS:
- Executive Summary
- Problem Statement
- Project Description
- Methodology and Approach
- Timeline
- Budget Overview
- Expected Outcomes and Impact
- Organization Capacity and Experience
- Sustainability Plan
- Evaluation and Monitoring

{self._add_custom_instructions(custom_instructions)}

Please generate a comprehensive proposal that follows best practices for grant writing and maximizes the chances of funding approval.
"""
            
            logger.debug("Generated proposal prompt")
            return prompt.strip()
            
        except Exception as e:
            logger.error(f"Error building proposal prompt: {str(e)}")
            raise
    
    def _format_organization_profile(self, profile: NGOProfile) -> str:
        """Format organization profile for the prompt"""
        try:
            profile_text = f"""
Organization Name: {profile.organization_name}
Mission Statement: {profile.mission_statement}
Focus Areas: {', '.join(profile.focus_areas) if profile.focus_areas else 'Not specified'}
Geographic Scope: {', '.join(profile.geographic_scope) if profile.geographic_scope else 'Not specified'}
Founded: {profile.founded_year or 'Not specified'}
Organization Type: {profile.organization_type or 'Not specified'}
Annual Budget Range: {profile.annual_budget_range or 'Not specified'}
Staff Size: {profile.staff_size or 'Not specified'}
Website: {profile.website or 'Not specified'}
"""
            
            if profile.programs_services:
                profile_text += f"\nPrograms and Services:\n"
                for program in profile.programs_services:
                    profile_text += f"- {program}\n"
            
            if profile.target_beneficiaries:
                profile_text += f"\nTarget Beneficiaries:\n"
                for beneficiary in profile.target_beneficiaries:
                    profile_text += f"- {beneficiary}\n"
            
            if profile.past_projects:
                profile_text += f"\nPast Projects:\n"
                for project in profile.past_projects:
                    if isinstance(project, dict):
                        profile_text += f"- {project.get('title', 'Untitled')}: {project.get('description', 'No description')}\n"
                    else:
                        profile_text += f"- {project}\n"
            
            if profile.partnerships:
                profile_text += f"\nKey Partnerships: {', '.join(profile.partnerships)}"
            
            if profile.funding_sources:
                profile_text += f"\nFunding Sources: {', '.join(profile.funding_sources)}"
            
            return profile_text.strip()
            
        except Exception as e:
            logger.error(f"Error formatting organization profile: {str(e)}")
            return "Organization profile formatting error"
    
    def _format_funding_opportunity(self, funding_opportunity: FundingOpportunity) -> str:
        """Format funding opportunity for the prompt"""
        try:
            opp_text = f"""
Title: {funding_opportunity.title}
Donor Organization: {funding_opportunity.donor_organization or 'Not specified'}
Funding Type: {funding_opportunity.funding_type or 'Not specified'}
Description: {funding_opportunity.description or 'Not specified'}
"""
            
            if funding_opportunity.amount_min or funding_opportunity.amount_max:
                amount_text = "Funding Amount: "
                if funding_opportunity.amount_min and funding_opportunity.amount_max:
                    amount_text += f"{funding_opportunity.currency or '$'}{funding_opportunity.amount_min:,.0f} - {funding_opportunity.currency or '$'}{funding_opportunity.amount_max:,.0f}"
                elif funding_opportunity.amount_max:
                    amount_text += f"Up to {funding_opportunity.currency or '$'}{funding_opportunity.amount_max:,.0f}"
                elif funding_opportunity.amount_min:
                    amount_text += f"Minimum {funding_opportunity.currency or '$'}{funding_opportunity.amount_min:,.0f}"
                opp_text += f"\n{amount_text}"
            
            if funding_opportunity.application_deadline:
                opp_text += f"\nApplication Deadline: {funding_opportunity.application_deadline.strftime('%Y-%m-%d')}"
            
            if funding_opportunity.focus_areas:
                opp_text += f"\nFocus Areas: {', '.join(funding_opportunity.focus_areas)}"
            
            if funding_opportunity.geographic_focus:
                opp_text += f"\nGeographic Focus: {', '.join(funding_opportunity.geographic_focus)}"
            
            if funding_opportunity.eligibility_criteria:
                opp_text += f"\nEligibility Criteria:\n"
                if isinstance(funding_opportunity.eligibility_criteria, list):
                    for criteria in funding_opportunity.eligibility_criteria:
                        opp_text += f"- {criteria}\n"
                else:
                    opp_text += f"- {funding_opportunity.eligibility_criteria}\n"
            
            if funding_opportunity.required_documents:
                opp_text += f"\nRequired Documents: {', '.join(funding_opportunity.required_documents)}"
            
            if funding_opportunity.application_process:
                opp_text += f"\nApplication Process: {funding_opportunity.application_process}"
            
            if funding_opportunity.keywords:
                opp_text += f"\nKeywords: {', '.join(funding_opportunity.keywords)}"
            
            return opp_text.strip()
            
        except Exception as e:
            logger.error(f"Error formatting funding opportunity: {str(e)}")
            return "Funding opportunity formatting error"
    
    def _add_custom_instructions(self, custom_instructions: Optional[str]) -> str:
        """Add custom instructions to the prompt"""
        if custom_instructions:
            return f"\nCUSTOM INSTRUCTIONS:\n{custom_instructions}\n"
        return ""
    
    def get_donor_template(self, donor_organization: Optional[str]) -> str:
        """Get donor-specific template"""
        return self.donor_templates.get_template(donor_organization)
    
    def build_enhancement_prompt(
        self,
        original_proposal: str,
        enhancement_request: str,
        funding_opportunity: FundingOpportunity
    ) -> str:
        """Build prompt for enhancing an existing proposal"""
        try:
            prompt = f"""
You are an expert grant writer. Please enhance the following proposal based on the specific enhancement request.

ORIGINAL PROPOSAL:
{original_proposal}

ENHANCEMENT REQUEST:
{enhancement_request}

FUNDING OPPORTUNITY CONTEXT:
{self._format_funding_opportunity(funding_opportunity)}

ENHANCEMENT GUIDELINES:
1. Maintain the overall structure and quality of the original proposal
2. Address the specific enhancement request thoroughly
3. Ensure all changes align with the funding opportunity requirements
4. Improve clarity, impact, and persuasiveness
5. Maintain professional grant writing standards
6. Keep the enhanced proposal cohesive and well-flowing

Please provide the enhanced proposal that addresses the requested improvements.
"""
            
            return prompt.strip()
            
        except Exception as e:
            logger.error(f"Error building enhancement prompt: {str(e)}")
            raise 