# Quick: Add Neon to Kiro (2 Minutes)

## 1. Get Neon API Key (1 min)
```
https://console.neon.tech
→ Profile → Account Settings → API Keys → Generate
→ Copy the key
```

## 2. Open Kiro MCP Settings (30 sec)
```
Ctrl+Shift+P → Type "MCP" → "Open MCP Settings"
```

## 3. Add This Code (30 sec)

In the `mcpServers` section, add:

```json
"neon": {
  "command": "npx",
  "args": ["-y", "@neondatabase/mcp-server-neon"],
  "env": {
    "NEON_API_KEY": "paste_your_api_key_here"
  },
  "disabled": false,
  "autoApprove": ["list-projects", "list-databases", "execute-query"]
}
```

### Full Example:
```json
{
  "mcpServers": {
    "neon": {
      "command": "npx",
      "args": ["-y", "@neondatabase/mcp-server-neon"],
      "env": {
        "NEON_API_KEY": "your_key_here"
      },
      "disabled": false,
      "autoApprove": ["list-projects", "list-databases"]
    }
  },
  "powers": {
    // ... existing powers ...
  }
}
```

## 4. Restart Kiro

Save file → Restart Kiro (or reload window)

## 5. Test It

Ask Kiro:
```
"List my Neon projects"
"Show my database connection string"
```

## Done! 🎉

Now you can:
- Query your database from Kiro
- Get connection strings
- Manage databases
- Run SQL directly

---

**See `KIRO_NEON_SETUP.md` for detailed instructions**
