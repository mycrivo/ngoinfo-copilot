from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from typing import Optional, Dict, Any
from models.ngo_profiles import NGOProfile
import logging

logger = logging.getLogger(__name__)


class NGOProfileManager:
    """
    Manages NGO profiles with simplified interface for prompt generation and scoring.
    
    This manager provides a simplified view of the comprehensive NGOProfile model,
    mapping between the simplified field structure and the full model.
    """
    
    def __init__(self, db_session: AsyncSession):
        self.db_session = db_session
    
    async def get_profile(self, user_id: int) -> Optional[Dict[str, Any]]:
        """
        Returns the full profile or None.
        
        Args:
            user_id: The user ID to fetch profile for
            
        Returns:
            Dict containing simplified profile data or None if not found
        """
        try:
            # Convert user_id to string for consistency with existing model
            user_id_str = str(user_id)
            
            result = await self.db_session.execute(
                select(NGOProfile).where(
                    NGOProfile.user_id == user_id_str,
                    NGOProfile.is_active == True
                )
            )
            profile = result.scalar_one_or_none()
            
            if not profile:
                return None
            
            # Map comprehensive model to simplified structure
            simplified_profile = {
                "user_id": user_id,
                "org_name": profile.organization_name,
                "mission": profile.mission_statement,
                "sectors": profile.focus_areas or [],
                "countries": profile.geographic_scope or [],
                "past_projects": self._format_past_projects(profile.past_projects),
                "staffing": profile.staff_size or ""
            }
            
            logger.debug(f"Retrieved profile for user {user_id}")
            return simplified_profile
            
        except Exception as e:
            logger.error(f"Error retrieving profile for user {user_id}: {str(e)}")
            return None
    
    async def create_or_update_profile(self, user_id: int, data: Dict[str, Any]) -> bool:
        """
        Upserts a profile based on user ID.
        
        Args:
            user_id: The user ID to create/update profile for
            data: Dictionary containing profile data with keys:
                - org_name: Organization name
                - mission: Mission statement
                - sectors: List of focus sectors
                - countries: List of countries/geographic scope
                - past_projects: Description of past projects
                - staffing: Staffing information
                
        Returns:
            bool: True if successful, False otherwise
        """
        try:
            user_id_str = str(user_id)
            
            # Check if profile exists
            result = await self.db_session.execute(
                select(NGOProfile).where(
                    NGOProfile.user_id == user_id_str,
                    NGOProfile.is_active == True
                )
            )
            existing_profile = result.scalar_one_or_none()
            
            if existing_profile:
                # Update existing profile
                existing_profile.organization_name = data.get("org_name", existing_profile.organization_name)
                existing_profile.mission_statement = data.get("mission", existing_profile.mission_statement)
                existing_profile.focus_areas = data.get("sectors", existing_profile.focus_areas)
                existing_profile.geographic_scope = data.get("countries", existing_profile.geographic_scope)
                existing_profile.staff_size = data.get("staffing", existing_profile.staff_size)
                
                # Handle past_projects - convert to structured format if needed
                if "past_projects" in data:
                    existing_profile.past_projects = self._structure_past_projects(data["past_projects"])
                
                # Recalculate completeness score
                existing_profile.profile_completeness_score = self.score_profile_data(data)
                
                logger.info(f"Updated profile for user {user_id}")
            else:
                # Create new profile
                new_profile = NGOProfile(
                    user_id=user_id_str,
                    organization_name=data.get("org_name", ""),
                    mission_statement=data.get("mission", ""),
                    focus_areas=data.get("sectors", []),
                    geographic_scope=data.get("countries", []),
                    staff_size=data.get("staffing", ""),
                    past_projects=self._structure_past_projects(data.get("past_projects", "")),
                    profile_completeness_score=self.score_profile_data(data)
                )
                
                self.db_session.add(new_profile)
                logger.info(f"Created new profile for user {user_id}")
            
            await self.db_session.commit()
            return True
            
        except IntegrityError as e:
            await self.db_session.rollback()
            logger.error(f"Integrity error creating/updating profile for user {user_id}: {str(e)}")
            return False
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error creating/updating profile for user {user_id}: {str(e)}")
            return False
    
    async def structure_for_prompt(self, user_id: int) -> Dict[str, Any]:
        """
        Returns a cleaned, structured dict with key profile values for prompt injection.
        
        Args:
            user_id: The user ID to get structured data for
            
        Returns:
            Dict containing cleaned profile data optimized for AI prompts
        """
        try:
            profile = await self.get_profile(user_id)
            
            if not profile:
                return {
                    "org_name": "Unknown Organization",
                    "mission": "Mission not provided",
                    "sectors": [],
                    "countries": [],
                    "past_projects": "No past projects listed",
                    "staffing": "Staffing information not provided"
                }
            
            # Clean and structure data for prompt use
            structured_data = {
                "org_name": profile["org_name"] or "Unknown Organization",
                "mission": profile["mission"] or "Mission not provided",
                "sectors": profile["sectors"] if profile["sectors"] else ["General development"],
                "countries": profile["countries"] if profile["countries"] else ["Not specified"],
                "past_projects": profile["past_projects"] or "No past projects listed",
                "staffing": profile["staffing"] or "Staffing information not provided"
            }
            
            # Add formatted strings for easy prompt injection
            structured_data["sectors_text"] = ", ".join(structured_data["sectors"])
            structured_data["countries_text"] = ", ".join(structured_data["countries"])
            
            logger.debug(f"Structured profile data for prompt for user {user_id}")
            return structured_data
            
        except Exception as e:
            logger.error(f"Error structuring profile for prompt for user {user_id}: {str(e)}")
            # Return default structure on error
            return {
                "org_name": "Unknown Organization",
                "mission": "Mission not provided",
                "sectors": ["General development"],
                "countries": ["Not specified"],
                "past_projects": "No past projects listed",
                "staffing": "Staffing information not provided",
                "sectors_text": "General development",
                "countries_text": "Not specified"
            }
    
    async def score_profile(self, user_id: int) -> int:
        """
        Returns a Copilot Confidence Score (0–100) based on completeness.
        
        Scoring criteria:
        - +20: mission is filled
        - +20: sectors and countries both non-empty
        - +20: past_projects has at least 200 characters
        - +20: staffing is filled
        - +20: org_name is present and >3 chars
        
        Args:
            user_id: The user ID to score profile for
            
        Returns:
            int: Score from 0 to 100
        """
        try:
            profile = await self.get_profile(user_id)
            
            if not profile:
                return 0
            
            return self.score_profile_data(profile)
            
        except Exception as e:
            logger.error(f"Error scoring profile for user {user_id}: {str(e)}")
            return 0
    
    def score_profile_data(self, data: Dict[str, Any]) -> int:
        """
        Score profile data based on completeness criteria.
        
        Args:
            data: Profile data dictionary
            
        Returns:
            int: Score from 0 to 100
        """
        score = 0
        
        # +20: mission is filled
        if data.get("mission") and len(data["mission"].strip()) > 0:
            score += 20
        
        # +20: sectors and countries both non-empty
        sectors = data.get("sectors", [])
        countries = data.get("countries", [])
        if sectors and len(sectors) > 0 and countries and len(countries) > 0:
            score += 20
        
        # +20: past_projects has at least 200 characters
        past_projects = data.get("past_projects", "")
        if past_projects and len(past_projects.strip()) >= 200:
            score += 20
        
        # +20: staffing is filled
        if data.get("staffing") and len(data["staffing"].strip()) > 0:
            score += 20
        
        # +20: org_name is present and >3 chars
        org_name = data.get("org_name", "")
        if org_name and len(org_name.strip()) > 3:
            score += 20
        
        return min(score, 100)  # Ensure maximum of 100
    
    def _format_past_projects(self, past_projects_data: Any) -> str:
        """
        Format past projects data from the comprehensive model to simple text.
        
        Args:
            past_projects_data: Past projects data (could be list of dicts or string)
            
        Returns:
            str: Formatted past projects text
        """
        if not past_projects_data:
            return ""
        
        if isinstance(past_projects_data, str):
            return past_projects_data
        
        if isinstance(past_projects_data, list):
            formatted_projects = []
            for project in past_projects_data:
                if isinstance(project, dict):
                    title = project.get("title", "Untitled Project")
                    description = project.get("description", "No description provided")
                    formatted_projects.append(f"{title}: {description}")
                else:
                    formatted_projects.append(str(project))
            return "; ".join(formatted_projects)
        
        return str(past_projects_data)
    
    def _structure_past_projects(self, past_projects_text: str) -> list:
        """
        Convert simple past projects text to structured format for the comprehensive model.
        
        Args:
            past_projects_text: Simple text description of past projects
            
        Returns:
            list: Structured past projects data
        """
        if not past_projects_text or not past_projects_text.strip():
            return []
        
        # For simplicity, treat the entire text as a single project
        return [{"title": "Past Work", "description": past_projects_text.strip()}] 