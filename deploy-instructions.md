# 部署說明書

## 🚀 GitHub 上傳步驟

### 第一步：身份驗證設定

由於 GitHub 已移除密碼認證，您需要使用以下方法之一：

#### 方法 A：使用 Personal Access Token (推薦)

1. **建立 Personal Access Token**：
   - 登入 GitHub
   - 進入 `Settings > Developer settings > Personal access tokens > Tokens (classic)`
   - 點擊 `Generate new token (classic)`
   - 勾選 `repo` 權限
   - 點擊 `Generate token`
   - **重要**：複製 token（只會顯示一次）

2. **使用 Token 推送**：
```bash
cd "/mnt/c/claude code/維修單線上簽名表單/maintenance-signature-system"

# 使用 token 推送（將 YOUR_TOKEN 替換為實際 token）
git push https://YOUR_TOKEN@github.com/jameslai-sparkofy/maintenance-signature-system.git main
```

#### 方法 B：使用 SSH 金鑰

1. **生成 SSH 金鑰**：
```bash
ssh-keygen -t ed25519 -C "jameslai.sparkofy@gmail.com"
```

2. **添加公鑰到 GitHub**：
```bash
# 顯示公鑰內容
cat ~/.ssh/id_ed25519.pub
```
   - 複製公鑰內容
   - 在 GitHub `Settings > SSH and GPG keys` 添加

3. **修改遠端 URL 並推送**：
```bash
git remote set-url origin git@github.com:jameslai-sparkofy/maintenance-signature-system.git
git push -u origin main
```

## 📋 GitHub Actions 設定

代碼推送成功後，請在 GitHub 倉庫設定以下 Secrets：

### 必要的 Secrets

進入您的 GitHub 倉庫：
`https://github.com/jameslai-sparkofy/maintenance-signature-system/settings/secrets/actions`

點擊 `New repository secret` 添加：

| Secret 名稱 | 說明 | 範例值 |
|------------|------|--------|
| `CLOUDWAYS_HOST` | Cloudways 伺服器 IP 地址 | `203.0.113.123` |
| `CLOUDWAYS_USERNAME` | SSH 使用者名稱 | `master_abc123` |
| `CLOUDWAYS_SSH_KEY` | SSH 私鑰完整內容 | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `CLOUDWAYS_PORT` | SSH 連接埠（通常是 22） | `22` |
| `WORDPRESS_PATH` | WordPress 安裝完整路徑 | `/home/master_abc123/applications/xyz_wordpress/public_html` |
| `DB_HOST` | 資料庫主機 | `localhost` |
| `DB_USER` | 資料庫使用者名稱 | `xyz_db_user` |
| `DB_PASSWORD` | 資料庫密碼 | `your_secure_password` |
| `DB_NAME` | 資料庫名稱 | `xyz_wordpress` |
| `BACKUP_PATH` | 備份存放路徑 | `/home/master_abc123/backups` |

### 如何獲取 Cloudways 資訊

1. **登入 Cloudways 控制台**
2. **選擇您的伺服器**
3. **獲取連接資訊**：
   - 在 `Server Management > SSH Keys` 查看/新增 SSH 金鑰
   - 在 `Server Management > Master Credentials` 查看使用者名稱
   - 伺服器 IP 在伺服器總覽頁面

4. **獲取應用程式路徑**：
   - 選擇 WordPress 應用程式
   - 在 `Application Management > Application Settings` 查看路徑
   - 完整路徑通常是：`/home/[master_username]/applications/[app_name]/public_html`

5. **獲取資料庫資訊**：
   - 在 `Application Management > Database` 查看資料庫設定

## 🔧 本地快速推送命令

如果您已經設定好身份驗證，可以直接執行：

```bash
# 進入插件目錄
cd "/mnt/c/claude code/維修單線上簽名表單/maintenance-signature-system"

# 檢查狀態
git status

# 推送到 GitHub（使用您選擇的身份驗證方法）
git push -u origin main
```

## ✅ 驗證部署

代碼推送成功後：

1. **檢查 GitHub Actions**：
   - 進入 `https://github.com/jameslai-sparkofy/maintenance-signature-system/actions`
   - 查看是否有自動觸發的工作流程

2. **檢查 WordPress**：
   - 登入您的 WordPress 後台
   - 確認插件已成功更新

## 🆘 常見問題

### Q: 推送時出現身份驗證錯誤
A: 請確認您使用的是 Personal Access Token 或 SSH 金鑰，GitHub 已不支援密碼驗證。

### Q: GitHub Actions 部署失敗
A: 檢查所有 Secrets 是否正確設定，特別是路徑和權限。

### Q: SSH 連接失敗
A: 確認 SSH 金鑰已正確添加到 Cloudways，並且防火牆允許連接。

## 📞 技術支援

如需協助，請查看：
- GitHub 倉庫的 Issues 頁面
- Cloudways 文檔
- WordPress 插件開發指南