# Cloudways 詳細設定指南

## 🌟 第一步：準備 Cloudways 環境

### 1.1 登入 Cloudways
1. 訪問 https://www.cloudways.com
2. 登入您的帳號
3. 選擇您的 WordPress 應用程式

### 1.2 獲取伺服器基本資訊
在 Cloudways 控制台記錄以下資訊：

**伺服器概覽頁面**：
- 伺服器 IP 地址：`_____._____._____.____`
- 伺服器位置：`____________`

**應用程式概覽頁面**：
- 應用程式名稱：`____________`
- 應用程式 URL：`https://____________`

## 🔐 第二步：設定 SSH 存取

### 2.1 生成 SSH 金鑰對

在您的本地電腦執行：

```bash
# 生成新的 SSH 金鑰
ssh-keygen -t ed25519 -C "jameslai.sparkofy@gmail.com" -f ~/.ssh/cloudways_maintenance

# 查看公鑰內容（需要複製）
cat ~/.ssh/cloudways_maintenance.pub

# 查看私鑰內容（需要複製到 GitHub Secrets）
cat ~/.ssh/cloudways_maintenance
```

### 2.2 在 Cloudways 添加 SSH 公鑰

1. **進入 SSH 金鑰管理**：
   - 在 Cloudways 控制台
   - 點擊 `Servers` 選擇您的伺服器
   - 左側選單選擇 `SSH Keys`

2. **添加公鑰**：
   - 點擊 `Add SSH Key`
   - 標籤：`GitHub Actions Deploy Key`
   - 貼上剛才複製的公鑰內容
   - 點擊 `Add Key`

### 2.3 測試 SSH 連接

```bash
# 測試連接（替換為實際的使用者名稱和 IP）
ssh -i ~/.ssh/cloudways_maintenance master_username@YOUR_SERVER_IP

# 如果成功連接，輸入 exit 退出
exit
```

## 📁 第三步：確認 WordPress 路徑結構

### 3.1 通過 SSH 檢查路徑

連接到您的伺服器：

```bash
ssh -i ~/.ssh/cloudways_maintenance master_username@YOUR_SERVER_IP

# 查看主目錄結構
ls -la /home/master_*/

# 查看應用程式目錄
ls -la /home/master_*/applications/

# 查看 WordPress 目錄
ls -la /home/master_*/applications/*/public_html/

# 查看插件目錄
ls -la /home/master_*/applications/*/public_html/wp-content/plugins/

# 記錄完整路徑
pwd
```

常見的路徑格式：
- `/home/master_abc123/applications/xyz_wordpress/public_html`

### 3.2 建立備份目錄

```bash
# 建立備份目錄
mkdir -p /home/master_username/backups

# 設定權限
chmod 755 /home/master_username/backups
```

## 🗄️ 第四步：獲取資料庫資訊

### 4.1 在 Cloudways 控制台查看

1. **進入應用程式設定**：
   - 選擇您的 WordPress 應用程式
   - 點擊 `Application Management`
   - 選擇 `Database`

2. **記錄資料庫資訊**：
   - 資料庫名稱：`_________________`
   - 使用者名稱：`_________________`
   - 密碼：`_____________________`
   - 主機：通常是 `localhost`

### 4.2 測試資料庫連接

```bash
# 在伺服器上測試資料庫連接
mysql -h localhost -u DB_USERNAME -p DB_NAME

# 輸入密碼後，如果成功會看到 MySQL 提示符
# 輸入 exit; 退出
```

## ⚙️ 第五步：GitHub Secrets 設定清單

將以下資訊填入 GitHub Secrets：

### 必填 Secrets

```
CLOUDWAYS_HOST = YOUR_SERVER_IP
CLOUDWAYS_USERNAME = master_abc123
CLOUDWAYS_SSH_KEY = -----BEGIN OPENSSH PRIVATE KEY-----
(整個私鑰內容，包含開頭和結尾行)
-----END OPENSSH PRIVATE KEY-----

CLOUDWAYS_PORT = 22
WORDPRESS_PATH = /home/master_abc123/applications/xyz_wordpress/public_html
DB_HOST = localhost
DB_USER = xyz_db_user  
DB_PASSWORD = your_db_password
DB_NAME = xyz_wordpress_db
BACKUP_PATH = /home/master_abc123/backups
```

### 設定 GitHub Secrets 步驟

1. 訪問：`https://github.com/jameslai-sparkofy/maintenance-signature-system/settings/secrets/actions`
2. 點擊 `New repository secret`
3. 依序添加上述每個 Secret
4. 確保私鑰內容完整複製（包含開頭結尾行）

## 🧪 第六步：測試部署流程

### 6.1 手動觸發部署測試

1. **進入 GitHub Actions**：
   - 訪問：`https://github.com/jameslai-sparkofy/maintenance-signature-system/actions`

2. **檢查工作流程**：
   - 查看是否有 "Deploy to Cloudways" 工作流程
   - 如果沒有自動觸發，可以推送一個小改動

### 6.2 推送測試代碼

```bash
cd "/mnt/c/claude code/維修單線上簽名表單/maintenance-signature-system"

# 添加部署說明文件
git add deploy-instructions.md CLOUDWAYS_SETUP.md

# 提交更改
git commit -m "Add deployment documentation and Cloudways setup guide"

# 推送到 GitHub（會觸發自動部署）
git push origin main
```

### 6.3 監控部署狀態

1. 在 GitHub Actions 頁面監控部署進度
2. 檢查每個步驟是否成功
3. 如有錯誤，查看日誌並調整 Secrets

## ✅ 第七步：驗證部署結果

### 7.1 檢查 WordPress 插件

1. 登入您的 WordPress 後台
2. 進入 `插件 > 已安裝的插件`
3. 查看是否有 "維修單線上簽名系統"
4. 如果沒有，請啟用插件

### 7.2 測試插件功能

1. 在 WordPress 後台查看是否有 "維修單系統" 選單
2. 嘗試建立一個測試維修單
3. 檢查資料庫表是否已建立

### 7.3 檢查自動備份

SSH 連接到伺服器：

```bash
# 檢查備份目錄
ls -la /home/master_username/backups/

# 查看最新備份
ls -la /home/master_username/backups/$(date +%Y-%m-%d)/
```

## 🔄 第八步：設定持續部署

現在您的系統已設定完成！以後只需要：

1. **修改代碼**
2. **提交到 GitHub**：
   ```bash
   git add .
   git commit -m "Update feature XYZ"
   git push origin main
   ```
3. **自動部署**：GitHub Actions 會自動部署到 Cloudways

## 🆘 疑難排解

### 常見問題

**SSH 連接失敗**：
- 檢查 IP 地址是否正確
- 確認 SSH 金鑰已添加到 Cloudways
- 檢查防火牆設定

**資料庫連接錯誤**：
- 確認資料庫憑證正確
- 檢查資料庫是否存在
- 確認使用者權限

**文件權限問題**：
- 檢查 WordPress 目錄權限
- 確認 web 伺服器用戶有寫入權限

**GitHub Actions 失敗**：
- 檢查所有 Secrets 是否正確設定
- 查看錯誤日誌找出具體問題
- 確認 SSH 金鑰格式正確

需要協助時，請提供具體的錯誤訊息！