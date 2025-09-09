"""Initial schema with all models

Revision ID: 5e10fa46213f
Revises: 2a5fcfa95cd9
Create Date: 2025-09-09 11:45:56.519840

"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import postgresql

# revision identifiers, used by Alembic.
revision: str = '5e10fa46213f'
down_revision: Union[str, None] = '2a5fcfa95cd9'
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    # Create users table
    op.create_table('users',
        sa.Column('id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('name', sa.String(length=255), nullable=False),
        sa.Column('email', sa.String(length=255), nullable=False),
        sa.Column('password_hash', sa.String(length=255), nullable=False),
        sa.Column('is_admin', sa.Boolean(), nullable=False),
        sa.Column('is_active', sa.Boolean(), nullable=False),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_users_email'), 'users', ['email'], unique=True)
    
    # Create ngo_profiles table
    op.create_table('ngo_profiles',
        sa.Column('id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('user_id', sa.Integer(), nullable=False),
        sa.Column('org_name', sa.String(length=500), nullable=False),
        sa.Column('mission', sa.Text(), nullable=False),
        sa.Column('sectors', sa.JSON(), nullable=True),
        sa.Column('countries', sa.JSON(), nullable=True),
        sa.Column('past_projects', sa.JSON(), nullable=True),
        sa.Column('staffing', sa.Text(), nullable=True),
        sa.Column('confidence_score', sa.Float(), nullable=True),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_ngo_profiles_user_id'), 'ngo_profiles', ['user_id'], unique=True)
    
    # Create funding_opportunities table (read-only, external)
    op.create_table('funding_opportunities',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('title', sa.String(length=500), nullable=False),
        sa.Column('donor', sa.String(length=255), nullable=False),
        sa.Column('summary', sa.Text(), nullable=True),
        sa.Column('objectives', sa.Text(), nullable=True),
        sa.Column('eligibility', sa.Text(), nullable=True),
        sa.Column('deadline', sa.DateTime(), nullable=True),
        sa.Column('amount_min', sa.Float(), nullable=True),
        sa.Column('amount_max', sa.Float(), nullable=True),
        sa.Column('currency', sa.String(length=3), nullable=True),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    
    # Create proposals table
    op.create_table('proposals',
        sa.Column('id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('user_id', sa.String(length=255), nullable=False),
        sa.Column('ngo_profile_id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('funding_opportunity_id', sa.Integer(), nullable=False),
        sa.Column('title', sa.String(length=500), nullable=False),
        sa.Column('content', sa.Text(), nullable=False),
        sa.Column('executive_summary', sa.Text(), nullable=True),
        sa.Column('generation_prompt', sa.Text(), nullable=False),
        sa.Column('donor_template_used', sa.String(length=255), nullable=True),
        sa.Column('ai_model_used', sa.String(length=100), nullable=False),
        sa.Column('generation_timestamp', sa.DateTime(), nullable=False),
        sa.Column('confidence_score', sa.Float(), nullable=True),
        sa.Column('alignment_score', sa.Float(), nullable=True),
        sa.Column('completeness_score', sa.Float(), nullable=True),
        sa.Column('user_rating', sa.Integer(), nullable=True),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.ForeignKeyConstraint(['ngo_profile_id'], ['ngo_profiles.id'], ),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_proposals_user_id'), 'proposals', ['user_id'], unique=False)
    op.create_index(op.f('ix_proposals_funding_opportunity_id'), 'proposals', ['funding_opportunity_id'], unique=False)
    
    # Create usage table
    op.create_table('usage',
        sa.Column('id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('user_id', sa.String(length=255), nullable=False),
        sa.Column('action', sa.String(length=50), nullable=False),
        sa.Column('timestamp', sa.DateTime(), nullable=False),
        sa.Column('metadata', sa.JSON(), nullable=True),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_usage_user_id'), 'usage', ['user_id'], unique=False)
    op.create_index(op.f('ix_usage_action'), 'usage', ['action'], unique=False)
    op.create_index(op.f('ix_usage_timestamp'), 'usage', ['timestamp'], unique=False)
    
    # Create idempotency_keys table
    op.create_table('idempotency_keys',
        sa.Column('id', postgresql.UUID(as_uuid=True), nullable=False),
        sa.Column('user_id', sa.String(length=255), nullable=False),
        sa.Column('idempotency_key', sa.String(length=255), nullable=False),
        sa.Column('endpoint', sa.String(length=100), nullable=False),
        sa.Column('request_data', sa.JSON(), nullable=False),
        sa.Column('response_data', sa.JSON(), nullable=False),
        sa.Column('status_code', sa.Integer(), nullable=False),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('expires_at', sa.DateTime(), nullable=False),
        sa.PrimaryKeyConstraint('id')
    )
    op.create_index(op.f('ix_idempotency_keys_user_id'), 'idempotency_keys', ['user_id'], unique=False)
    op.create_index(op.f('ix_idempotency_keys_key'), 'idempotency_keys', ['idempotency_key'], unique=True)
    op.create_index(op.f('ix_idempotency_keys_expires_at'), 'idempotency_keys', ['expires_at'], unique=False)


def downgrade() -> None:
    # Drop tables in reverse order
    op.drop_index(op.f('ix_idempotency_keys_expires_at'), table_name='idempotency_keys')
    op.drop_index(op.f('ix_idempotency_keys_key'), table_name='idempotency_keys')
    op.drop_index(op.f('ix_idempotency_keys_user_id'), table_name='idempotency_keys')
    op.drop_table('idempotency_keys')
    
    op.drop_index(op.f('ix_usage_timestamp'), table_name='usage')
    op.drop_index(op.f('ix_usage_action'), table_name='usage')
    op.drop_index(op.f('ix_usage_user_id'), table_name='usage')
    op.drop_table('usage')
    
    op.drop_index(op.f('ix_proposals_funding_opportunity_id'), table_name='proposals')
    op.drop_index(op.f('ix_proposals_user_id'), table_name='proposals')
    op.drop_table('proposals')
    
    op.drop_table('funding_opportunities')
    
    op.drop_index(op.f('ix_ngo_profiles_user_id'), table_name='ngo_profiles')
    op.drop_table('ngo_profiles')
    
    op.drop_index(op.f('ix_users_email'), table_name='users')
    op.drop_table('users')
