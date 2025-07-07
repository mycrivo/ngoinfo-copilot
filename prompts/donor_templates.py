from typing import Optional, Dict
import logging

logger = logging.getLogger(__name__)


class DonorTemplates:
    """Donor-specific templates and guidelines for proposal generation"""
    
    def __init__(self):
        self.templates = {
            "gates_foundation": self._gates_foundation_template(),
            "ford_foundation": self._ford_foundation_template(),
            "open_society_foundations": self._open_society_template(),
            "usaid": self._usaid_template(),
            "european_union": self._eu_template(),
            "world_bank": self._world_bank_template(),
            "united_nations": self._un_template(),
            "default": self._default_template()
        }
    
    def get_template(self, donor_organization: Optional[str]) -> str:
        """
        Get donor-specific template
        
        Args:
            donor_organization: Name of the donor organization
            
        Returns:
            str: Donor-specific template or default template
        """
        if not donor_organization:
            return self.templates["default"]
        
        # Normalize donor name for lookup
        donor_key = self._normalize_donor_name(donor_organization)
        
        # Return specific template or default
        return self.templates.get(donor_key, self.templates["default"])
    
    def _normalize_donor_name(self, donor_name: str) -> str:
        """Normalize donor name for template lookup"""
        normalized = donor_name.lower().replace(" ", "_").replace("-", "_")
        
        # Map common variations
        if "gates" in normalized:
            return "gates_foundation"
        elif "ford" in normalized:
            return "ford_foundation"
        elif "open_society" in normalized or "soros" in normalized:
            return "open_society_foundations"
        elif "usaid" in normalized or "us_aid" in normalized:
            return "usaid"
        elif "european" in normalized and "union" in normalized:
            return "european_union"
        elif "world_bank" in normalized:
            return "world_bank"
        elif "united_nations" in normalized or "un_" in normalized:
            return "united_nations"
        
        return "default"
    
    def _gates_foundation_template(self) -> str:
        """Gates Foundation specific guidelines"""
        return """
GATES FOUNDATION SPECIFIC GUIDELINES:
- Emphasize measurable impact and evidence-based approaches
- Focus on innovation and scalability
- Highlight data-driven decision making
- Include strong monitoring and evaluation framework
- Demonstrate sustainability and long-term impact
- Use clear, concise language with specific metrics
- Show how the project addresses global challenges
- Include risk mitigation strategies
- Emphasize collaboration and partnerships
- Focus on underserved populations and equity
"""
    
    def _ford_foundation_template(self) -> str:
        """Ford Foundation specific guidelines"""
        return """
FORD FOUNDATION SPECIFIC GUIDELINES:
- Emphasize social justice and human rights
- Focus on building more just societies
- Highlight work with marginalized communities
- Include participatory approaches and community engagement
- Demonstrate commitment to equity and inclusion
- Show how the project addresses systemic issues
- Include capacity building and empowerment strategies
- Emphasize long-term social change
- Include diverse perspectives and voices
- Focus on reducing inequality
"""
    
    def _open_society_template(self) -> str:
        """Open Society Foundations specific guidelines"""
        return """
OPEN SOCIETY FOUNDATIONS SPECIFIC GUIDELINES:
- Emphasize democracy, human rights, and rule of law
- Focus on transparency and accountability
- Highlight work with civil society organizations
- Include advocacy and policy reform components
- Demonstrate commitment to open societies
- Show how the project promotes civic engagement
- Include media freedom and information access
- Emphasize justice and equality
- Focus on protecting vulnerable populations
- Include capacity building for civil society
"""
    
    def _usaid_template(self) -> str:
        """USAID specific guidelines"""
        return """
USAID SPECIFIC GUIDELINES:
- Follow USAID's development objectives and priorities
- Include clear development hypothesis and theory of change
- Emphasize sustainability and local ownership
- Include strong results framework with indicators
- Demonstrate cost-effectiveness and value for money
- Show alignment with country development strategies
- Include gender integration and youth engagement
- Emphasize partnership with local organizations
- Include environmental compliance considerations
- Focus on building local capacity and systems
"""
    
    def _eu_template(self) -> str:
        """European Union specific guidelines"""
        return """
EUROPEAN UNION SPECIFIC GUIDELINES:
- Align with EU development policies and priorities
- Emphasize European values and principles
- Include multi-stakeholder approaches
- Demonstrate complementarity with other EU actions
- Show clear European added value
- Include visibility and communication requirements
- Emphasize partnerships between EU and partner countries
- Include conflict sensitivity and do no harm approaches
- Focus on sustainable development goals
- Include innovation and knowledge sharing
"""
    
    def _world_bank_template(self) -> str:
        """World Bank specific guidelines"""
        return """
WORLD BANK SPECIFIC GUIDELINES:
- Align with World Bank Group strategy and priorities
- Emphasize poverty reduction and shared prosperity
- Include strong economic analysis and justification
- Demonstrate institutional capacity and governance
- Show environmental and social safeguards compliance
- Include results-based approach with clear indicators
- Emphasize knowledge sharing and learning
- Include private sector engagement where relevant
- Focus on scalability and replicability
- Demonstrate fiscal sustainability
"""
    
    def _un_template(self) -> str:
        """United Nations specific guidelines"""
        return """
UNITED NATIONS SPECIFIC GUIDELINES:
- Align with UN Sustainable Development Goals
- Emphasize multilateral cooperation and partnerships
- Include human rights-based approach
- Demonstrate conflict sensitivity and peace building
- Show gender equality and women's empowerment
- Include youth engagement and participation
- Emphasize leaving no one behind principle
- Include climate change and environmental considerations
- Focus on capacity building and institution strengthening
- Demonstrate coordination with UN country teams
"""
    
    def _default_template(self) -> str:
        """Default template for unknown donors"""
        return """
GENERAL BEST PRACTICES:
- Use clear, professional language
- Include specific, measurable outcomes
- Demonstrate organizational capacity and experience
- Show clear alignment with donor priorities
- Include realistic timeline and budget
- Emphasize sustainability and long-term impact
- Include strong monitoring and evaluation plan
- Show evidence-based approach
- Include risk management strategies
- Demonstrate stakeholder engagement
- Focus on innovation and best practices
- Include clear communication and reporting plans
"""
    
    def get_all_templates(self) -> Dict[str, str]:
        """Get all available templates"""
        return self.templates.copy()
    
    def add_custom_template(self, donor_key: str, template: str) -> None:
        """Add a custom template for a specific donor"""
        self.templates[donor_key] = template
        logger.info(f"Added custom template for {donor_key}")
    
    def get_template_keys(self) -> list:
        """Get list of all template keys"""
        return list(self.templates.keys()) 