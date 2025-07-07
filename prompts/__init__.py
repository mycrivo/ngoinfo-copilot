"""
Prompt templates and builders for NGOInfo-Copilot
"""

from .prompt_builder import PromptBuilder
from .donor_templates import DonorTemplates
from .proposal_prompt_factory import build_proposal_prompt

__all__ = ["PromptBuilder", "DonorTemplates", "build_proposal_prompt"] 