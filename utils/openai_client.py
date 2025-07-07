import openai
import os
from typing import Dict, Any, Optional
import logging
import json

logger = logging.getLogger(__name__)


class OpenAIClient:
    """OpenAI API client for proposal generation"""
    
    def __init__(self):
        self.api_key = os.getenv("OPENAI_API_KEY")
        if not self.api_key:
            raise ValueError("OPENAI_API_KEY environment variable is required")
        
        # Use the new AsyncOpenAI client
        self.client = openai.AsyncOpenAI(api_key=self.api_key)
        self.default_model = "gpt-4"
        self.max_tokens = 4000
        self.temperature = 0.7
    
    async def generate_proposal(self, prompt: str, model: Optional[str] = None) -> Dict[str, Any]:
        """
        Generate a proposal using OpenAI API
        
        Args:
            prompt: The complete prompt for proposal generation
            model: Optional model override (defaults to gpt-4)
            
        Returns:
            Dict containing generated proposal content and metadata
        """
        try:
            model_to_use = model or self.default_model
            
            response = await self.client.chat.completions.create(
                model=model_to_use,
                messages=[
                    {
                        "role": "system",
                        "content": "You are an expert grant writer specializing in NGO proposals. Generate comprehensive, professional proposals that align with funding requirements and showcase the organization's capabilities."
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=self.max_tokens,
                temperature=self.temperature,
                top_p=1.0,
                frequency_penalty=0.0,
                presence_penalty=0.0
            )
            
            # Extract content from response
            content = response.choices[0].message.content or ""
            
            # Try to parse structured response if formatted as JSON
            try:
                parsed_content = json.loads(content)
                if isinstance(parsed_content, dict) and "content" in parsed_content:
                    return {
                        "content": parsed_content["content"],
                        "title": parsed_content.get("title"),
                        "executive_summary": parsed_content.get("executive_summary"),
                        "model": model_to_use,
                        "usage": {
                            "prompt_tokens": response.usage.prompt_tokens if response.usage else 0,
                            "completion_tokens": response.usage.completion_tokens if response.usage else 0,
                            "total_tokens": response.usage.total_tokens if response.usage else 0
                        }
                    }
            except json.JSONDecodeError:
                pass
            
            # If not JSON, treat as plain text
            return {
                "content": content,
                "title": self._extract_title(content) if content else None,
                "executive_summary": self._extract_executive_summary(content) if content else None,
                "model": model_to_use,
                "usage": {
                    "prompt_tokens": response.usage.prompt_tokens if response.usage else 0,
                    "completion_tokens": response.usage.completion_tokens if response.usage else 0,
                    "total_tokens": response.usage.total_tokens if response.usage else 0
                }
            }
            
        except Exception as e:
            logger.error(f"Error generating proposal with OpenAI: {str(e)}")
            raise
    
    def _extract_title(self, content: str) -> Optional[str]:
        """Extract title from proposal content"""
        try:
            lines = content.split('\n')
            for line in lines:
                line = line.strip()
                if line and not line.startswith('#'):
                    # First non-empty, non-header line is likely the title
                    return line
            return None
        except Exception as e:
            logger.error(f"Error extracting title: {str(e)}")
            return None
    
    def _extract_executive_summary(self, content: str) -> Optional[str]:
        """Extract executive summary from proposal content"""
        try:
            content_lower = content.lower()
            
            # Look for executive summary section
            summary_start = content_lower.find("executive summary")
            if summary_start == -1:
                return None
            
            # Find the end of the executive summary section
            lines = content[summary_start:].split('\n')
            summary_lines = []
            in_summary = False
            
            for line in lines:
                line = line.strip()
                if "executive summary" in line.lower():
                    in_summary = True
                    continue
                
                if in_summary:
                    if line and not line.startswith('#'):
                        summary_lines.append(line)
                    elif line.startswith('#') and len(summary_lines) > 0:
                        # Hit next section
                        break
            
            if summary_lines:
                return ' '.join(summary_lines)
            
            return None
            
        except Exception as e:
            logger.error(f"Error extracting executive summary: {str(e)}")
            return None
    
    async def enhance_proposal(self, proposal_content: str, enhancement_instructions: str) -> Dict[str, Any]:
        """
        Enhance an existing proposal based on feedback
        
        Args:
            proposal_content: The existing proposal content
            enhancement_instructions: Instructions for improvement
            
        Returns:
            Dict containing enhanced proposal content
        """
        try:
            prompt = f"""
            Please enhance the following proposal based on the given instructions:
            
            Original Proposal:
            {proposal_content}
            
            Enhancement Instructions:
            {enhancement_instructions}
            
            Please provide an improved version that addresses the instructions while maintaining the overall structure and quality.
            """
            
            return await self.generate_proposal(prompt)
            
        except Exception as e:
            logger.error(f"Error enhancing proposal: {str(e)}")
            raise
    
    async def summarize_proposal(self, proposal_content: str) -> str:
        """
        Generate a summary of a proposal
        
        Args:
            proposal_content: The proposal content to summarize
            
        Returns:
            String containing the summary
        """
        try:
            response = await self.client.chat.completions.create(
                model="gpt-3.5-turbo",  # Use faster model for summaries
                messages=[
                    {
                        "role": "system",
                        "content": "You are a skilled summarizer. Create concise, informative summaries of grant proposals."
                    },
                    {
                        "role": "user",
                        "content": f"Please provide a brief summary of this proposal in 2-3 sentences:\n\n{proposal_content[:2000]}"
                    }
                ],
                max_tokens=200,
                temperature=0.3
            )
            
            return response.choices[0].message.content or "Summary not available"
            
        except Exception as e:
            logger.error(f"Error summarizing proposal: {str(e)}")
            return "Summary not available" 