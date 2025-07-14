from fastapi import APIRouter, Request
from fastapi.templating import Jinja2Templates
from fastapi.responses import HTMLResponse

# Initialize Jinja2 templates
templates = Jinja2Templates(directory="templates")

router = APIRouter()


@router.get("/admin-test-ui", response_class=HTMLResponse)
async def admin_test_ui(request: Request):
    """
    Serve the admin test UI for proposal generation testing
    """
    return templates.TemplateResponse("admin-test-ui.html", {"request": request}) 