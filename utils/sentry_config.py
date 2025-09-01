"""
Sentry configuration for error tracking and performance monitoring
"""
import os
import sentry_sdk
from sentry_sdk.integrations.fastapi import FastApiIntegration
from sentry_sdk.integrations.sqlalchemy import SqlalchemyIntegration
from utils.logging_config import request_id_ctx_var


def setup_sentry():
    """Initialize Sentry if DSN is provided"""
    sentry_dsn = os.getenv("SENTRY_DSN")
    
    if not sentry_dsn:
        return
    
    environment = os.getenv("ENV", "development")
    app_name = os.getenv("APP_NAME", "ngoinfo-copilot")
    
    def before_send(event, hint):
        """Process events before sending to Sentry"""
        # Add request ID to event context
        request_id = request_id_ctx_var.get("")
        if request_id:
            event.setdefault("tags", {})["request_id"] = request_id
            event.setdefault("extra", {})["request_id"] = request_id
        
        # Scrub sensitive data
        if "extra" in event:
            # Remove potential secrets from extra data
            sensitive_keys = [
                "password", "token", "key", "secret", "auth", "credential",
                "jwt", "bearer", "api_key", "openai"
            ]
            for key in list(event["extra"].keys()):
                if any(sensitive in key.lower() for sensitive in sensitive_keys):
                    event["extra"][key] = "[REDACTED]"
        
        return event
    
    sentry_sdk.init(
        dsn=sentry_dsn,
        environment=environment,
        release=f"{app_name}@1.0.0",
        before_send=before_send,
        integrations=[
            FastApiIntegration(
                auto_enabling_integrations=False,
                transaction_style="endpoint"
            ),
            SqlalchemyIntegration()
        ],
        # Performance monitoring
        traces_sample_rate=0.1 if environment == "production" else 1.0,
        # Error sampling
        sample_rate=1.0,
        # Additional options
        attach_stacktrace=True,
        send_default_pii=False,  # Don't send personally identifiable information
        max_breadcrumbs=50,
    )


def add_sentry_context(key: str, value: any):
    """Add context to Sentry scope"""
    with sentry_sdk.configure_scope() as scope:
        scope.set_context(key, value)


def capture_exception(exc: Exception, **kwargs):
    """Capture exception with additional context"""
    with sentry_sdk.configure_scope() as scope:
        # Add request ID as tag
        request_id = request_id_ctx_var.get("")
        if request_id:
            scope.set_tag("request_id", request_id)
        
        # Add any additional context
        for key, value in kwargs.items():
            scope.set_extra(key, value)
        
        sentry_sdk.capture_exception(exc)
