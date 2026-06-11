import requests

BASE_URL = "http://localhost:8000"
LOGIN_PATH = "/api/auth/login"
REGISTER_PATH = "/api/auth/register"
TIMEOUT = 30


def test_postapiauthloginwithvalidcredentials():
    # Use a unique email to avoid conflicts if necessary
    test_user = {
        "name": "Test User",
        "email": "testuser_login_valid@example.com",
        "password": "TestPassword123!"
    }

    # Register the user first to ensure user exists for login
    try:
        reg_response = requests.post(
            BASE_URL + REGISTER_PATH,
            json={
                "name": test_user["name"],
                "email": test_user["email"],
                "password": test_user["password"]
            },
            timeout=TIMEOUT
        )
        # 201 Created or 422 if user already exists
        assert reg_response.status_code in [201, 422]

        # Now attempt login with valid credentials
        login_response = requests.post(
            BASE_URL + LOGIN_PATH,
            json={
                "email": test_user["email"],
                "password": test_user["password"]
            },
            timeout=TIMEOUT
        )
        assert login_response.status_code == 200

        login_json = login_response.json()

        # Validate presence of JWT token and user data keys
        assert isinstance(login_json, dict)
        # Assuming token key, often called 'token' or 'access_token'
        assert any(k for k in login_json if "token" in k.lower()), "JWT token not found in response"
        # Assuming user data presence, e.g., 'user' key or keys like 'id', 'email' etc.
        user_data_keys = ['user', 'id', 'email', 'name']
        assert any(key in login_json for key in user_data_keys), "User data not found in response"

    except requests.RequestException as e:
        raise AssertionError(f"Request failed: {e}")


test_postapiauthloginwithvalidcredentials()
