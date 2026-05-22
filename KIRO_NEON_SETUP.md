# Adding Neon Database to Kiro

## Step 1: Get Your Neon API Key

1. Go to https://console.neon.tech
2. Click on your profile (top right)
3. Go to **Account Settings**
4. Navigate to **API Keys** section
5. Click **Generate new API key**
6. Copy the API key (save it somewhere safe!)

## Step 2: Add Neon MCP Server to Kiro

### Option A: Using Kiro Command Palette (Easiest)

1. Open Kiro
2. Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
3. Type: **"MCP"**
4. Select: **"Open MCP Settings"**
5. This will open your `mcp.json` file

### Option B: Manual File Edit

Open this file in your editor:
```
C:\Users\Admin\.kiro\settings\mcp.json
```

## Step 3: Add Neon Configuration

Add this to your `mcp.json` file in the `mcpServers` section:

```json
{
  "mcpServers": {
    "neon": {
      "command": "npx",
      "args": [
        "-y",
        "@neondatabase/mcp-server-neon"
      ],
      "env": {
        "NEON_API_KEY": "your_neon_api_key_here"
      },
      "disabled": false,
      "autoApprove": [
        "list-projects",
        "list-databases",
        "execute-query"
      ]
    }
  },
  "powers": {
    // ... your existing powers configuration
  }
}
```

### Complete Example:

```json
{
  "mcpServers": {
    "neon": {
      "command": "npx",
      "args": [
        "-y",
        "@neondatabase/mcp-server-neon"
      ],
      "env": {
        "NEON_API_KEY": "your_actual_neon_api_key"
      },
      "disabled": false,
      "autoApprove": [
        "list-projects",
        "list-databases",
        "execute-query"
      ]
    }
  },
  "powers": {
    "mcpServers": {
      "power-postman-postman": {
        "url": "https://mcp.postman.com/minimal",
        "headers": {
          "Authorization": "Bearer your_postman_api_key"
        },
        "autoApprove": [
          "getAuthenticatedUser",
          "createWorkspace",
          "createEnvironment",
          "createCollection",
          "putCollection",
          "createCollectionRequest",
          "getEnabledTools",
          "getCollection"
        ]
      },
      "power-figma-figma": {
        "type": "http",
        "url": "https://mcp.figma.com/mcp"
      }
    }
  }
}
```

## Step 4: Replace the API Key

Replace `"your_actual_neon_api_key"` with the API key you copied from Step 1.

## Step 5: Restart Kiro

1. Save the `mcp.json` file
2. Restart Kiro or reload the window
3. The Neon MCP server will automatically connect

## Step 6: Verify It's Working

In Kiro chat, you can now ask:
```
"List my Neon projects"
"Show databases in my Neon project"
"Execute query: SELECT * FROM users LIMIT 5"
```

## Available Neon MCP Tools

Once configured, you can use these tools through Kiro:

- **list-projects** - List all your Neon projects
- **list-databases** - List databases in a project
- **execute-query** - Run SQL queries
- **create-database** - Create new databases
- **get-connection-string** - Get connection details

## Example Usage in Kiro

After setup, you can ask Kiro:

```
"Show me all tables in my campfix database"
"Run this query: SELECT COUNT(*) FROM users"
"Get the connection string for my production database"
"Create a new database called campfix_test"
```

## Troubleshooting

### Error: "npx command not found"
**Solution**: Install Node.js from https://nodejs.org

### Error: "Invalid API key"
**Solution**: 
1. Go back to Neon console
2. Generate a new API key
3. Update the `NEON_API_KEY` in `mcp.json`
4. Restart Kiro

### Error: "MCP server not connecting"
**Solution**:
1. Check the `mcp.json` syntax is valid JSON
2. Ensure no trailing commas
3. Restart Kiro
4. Check Kiro logs: Help > Toggle Developer Tools > Console

### Can't find mcp.json file
**Solution**:
1. Press `Ctrl+Shift+P` in Kiro
2. Type "MCP"
3. Select "Open MCP Settings"
4. This will create/open the file

## Alternative: Using Neon CLI Instead

If you prefer using the Neon CLI you already installed:

```json
{
  "mcpServers": {
    "neon-cli": {
      "command": "neonctl",
      "args": ["mcp"],
      "env": {
        "NEON_API_KEY": "your_neon_api_key"
      },
      "disabled": false,
      "autoApprove": []
    }
  }
}
```

## Security Note

⚠️ **Never commit `mcp.json` with your API keys to Git!**

The file is in your user directory (not in your project), so it won't be committed. But be careful when sharing screenshots or configuration files.

## Next Steps

After setting up Neon in Kiro:

1. ✅ You can query your database directly from Kiro
2. ✅ Get connection strings for your `.env.vercel`
3. ✅ Manage databases without leaving the editor
4. ✅ Run migrations and check results instantly

---

**Need help?** Ask Kiro: "Help me connect to my Neon database"
