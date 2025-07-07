from typing import Dict, Any
import logging

logger = logging.getLogger(__name__)


async def build_proposal_prompt(profile: Dict[str, Any], funding: Dict[str, Any]) -> str:
    """
    Generate the full OpenAI input prompt text for proposal generation.
    
    Args:
        profile: Structured dict from NGOProfileManager.structure_for_prompt(user_id)
        funding: Dict with keys: title, donor, summary, objectives, eligibility, 
                proposal_template_skeleton (optional)
    
    Returns:
        str: The final prompt string to be passed to OpenAI's GPT endpoint
    """
    try:
        # Extract funding information with defaults
        funding_title = funding.get("title", "Funding Opportunity")
        funding_donor = funding.get("donor", "Unknown Donor")
        funding_summary = funding.get("summary", "No summary provided")
        funding_objectives = funding.get("objectives", "No objectives specified")
        funding_eligibility = funding.get("eligibility", "No eligibility criteria specified")
        proposal_template_skeleton = funding.get("proposal_template_skeleton")
        
        # Extract profile information with defaults
        org_name = profile.get("org_name", "Organization")
        mission = profile.get("mission", "Mission not provided")
        sectors_text = profile.get("sectors_text", "General development")
        countries_text = profile.get("countries_text", "Not specified")
        past_projects = profile.get("past_projects", "No past projects listed")
        staffing = profile.get("staffing", "Staffing information not provided")
        
        # Check if custom proposal template skeleton exists
        if proposal_template_skeleton:
            # Use template with proposal_template_skeleton
            prompt = f"""You are a professional grant writer assisting NGOs in preparing high-quality, funder-aligned proposals.

Using the structured funding opportunity from NGOInfo and the NGO's verified profile, generate a persuasive, human-sounding grant proposal. The content must reflect the NGO's real strengths and experience, and align clearly with the funder's goals.

📌 FUNDING OPPORTUNITY FROM NGOINFO:
(This data is accurate and parsed from a real funding opportunity.)
- Title: {funding_title}
- Donor: {funding_donor}
- Summary: {funding_summary}
- Objectives: {funding_objectives}
- Eligibility: {funding_eligibility}

📌 NGO PROFILE:
- Name: {org_name}
- Mission: {mission}
- Focus Sectors: {sectors_text}
- Geographic Focus: {countries_text}
- Past Projects: {past_projects}
- Staffing Overview: {staffing}

📑 PROPOSAL STRUCTURE:
Follow this predefined section structure provided by NGOInfo's AI system:
{proposal_template_skeleton}

✍️ WRITING GUIDELINES:
- Use a human, empathetic, and professional tone.
- Match the language and priorities of the funding opportunity.
- Base all content on the NGO's actual profile — do not invent achievements or claims.
- Be specific and realistic. Avoid generic filler or AI-sounding text.
- Total length should be around 1000–1200 words.

Generate the full proposal, one section at a time, using only the section heading and the corresponding content."""
        
        else:
            # Use fallback template without proposal_template_skeleton
            prompt = f"""You are a professional grant writer supporting NGOs in creating persuasive, funder-ready proposals.

Use the following donor brief and NGO profile to generate a specific, credible, and compelling grant proposal. The proposal should be written in a natural, human tone — as if drafted by an experienced development professional — and closely reflect the NGO's actual history, mission, and capacity.

📌 DONOR BRIEF:
(Use this as the primary source for aligning with the funder's goals and language.)
- Title: {funding_title}
- Donor: {funding_donor}
- Summary: {funding_summary}
- Objectives: {funding_objectives}
- Eligibility: {funding_eligibility}

📌 NGO PROFILE:
- Name: {org_name}
- Mission: {mission}
- Focus Sectors: {sectors_text}
- Geographic Focus: {countries_text}
- Past Projects: {past_projects}
- Staffing Overview: {staffing}

📑 PROPOSAL STRUCTURE:
Use the following ordered sections:
1. Executive Summary
2. Organization Background
3. Problem Statement
4. Project Objectives
5. Methodology/Approach
6. Expected Results and Impact
7. Sustainability
8. Budget Overview (no real numbers)
9. Monitoring and Evaluation
10. Closing Statement

✍️ WRITING GUIDELINES:
- Maintain a professional and empathetic tone throughout.
- Avoid generic statements — every paragraph should reflect the NGO's real work and the donor's real priorities.
- If a section is unclear or the donor brief lacks detail, infer logically from the NGO's mission and past work.
- Output each section with only its heading and content. Do not include extra commentary, system notes, or generation hints.
- Total length should be approx. 1000–1200 words.

Generate the full proposal now, one section after another, based on the provided structure."""
        
        logger.debug(f"Generated proposal prompt for {org_name} targeting {funding_donor}")
        return prompt.strip()
        
    except Exception as e:
        logger.error(f"Error building proposal prompt: {str(e)}")
        # Return a basic fallback prompt in case of error
        return """You are a professional grant writer. Please generate a grant proposal based on the provided NGO profile and funding opportunity information. Create a comprehensive proposal with sections for executive summary, organization background, problem statement, objectives, methodology, expected results, sustainability, budget overview, monitoring and evaluation, and closing statement.""" 