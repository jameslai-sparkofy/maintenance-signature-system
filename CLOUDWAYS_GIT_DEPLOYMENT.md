# Cloudways Git 部署指南

## 🎯 概述

這份指南將幫您使用 Cloudways 原生的 Git 部署功能來部署維修單線上簽名系統。這比傳統的 SSH 部署更簡單、更可靠。

## 🚀 第一步：在 Cloudways 啟用 Git 部署

### 1.1 登入 Cloudways 控制台
1. 前往 https://platform.cloudways.com
2. 登入您的帳號
3. 選擇您的 WordPress 應用程式

### 1.2 啟用 Git 部署功能
1. 在應用程式頁面，點擊左側選單的 **「Git Deployment」**
2. 點擊 **「Enable Git」** 按鈕
3. 您會看到 Git 部署設定頁面

### 1.3 連接 GitHub 倉庫
填寫以下資訊：

- **Git Repository URL**: `https://github.com/jameslai-sparkofy/maintenance-signature-system.git`
- **Branch Name**: `main`
- **Deploy Path**: `wp-content/plugins/maintenance-signature-system`

### 1.4 設定部署金鑰
1. Cloudways 會生成一個 **Deploy Key**
2. 複製這個 Deploy Key
3. 前往 GitHub 倉庫設定：
   - 進入 `Settings > Deploy keys`
   - 點擊 `Add deploy key`
   - 標題：`Cloudways Deployment Key`
   - 貼上 Deploy Key
   - **不要**勾選 "Allow write access"
   - 點擊 `Add key`

## 🔧 第二步：設定 GitHub Webhooks（可選）

為了實現自動部署，您可以設定 GitHub Webhooks：

### 2.1 獲取 Cloudways Webhook URL
1. 在 Cloudways Git 部署頁面
2. 複製 **「Webhook URL」**
3. 記下這個 URL，格式類似：`https://api.cloudways.com/api/v1/git-deploy/webhook/xxx`

### 2.2 在 GitHub 設定 Webhook
1. 前往 GitHub 倉庫的 `Settings > Webhooks`
2. 點擊 `Add webhook`
3. 填寫：
   - **Payload URL**: 貼上 Cloudways Webhook URL
   - **Content type**: `application/json`
   - **Secret**: 留空
   - **Events**: 選擇 `Just the push event`
   - **Active**: 勾選
4. 點擊 `Add webhook`

## 📋 第三步：設定 GitHub Secrets

雖然使用 Cloudways Git 部署，我們仍需要一些 Secrets 來支援 GitHub Actions：

前往：`https://github.com/jameslai-sparkofy/maintenance-signature-system/settings/secrets/actions`

### 必要的 Secrets

| Secret 名稱 | 說明 | 範例值 |
|------------|------|--------|
| `CLOUDWAYS_WEBHOOK_URL` | Cloudways Webhook URL | `https://api.cloudways.com/api/v1/git-deploy/webhook/xxx` |
| `WORDPRESS_SITE_URL` | 您的 WordPress 網站 URL | `https://yoursite.com` |

### 可選的 Secrets（用於高級功能）

| Secret 名稱 | 說明 | 用途 |
|------------|------|------|
| `SLACK_WEBHOOK` | Slack 通知 URL | 部署通知 |
| `EMAIL_NOTIFICATION` | 通知郵件地址 | 部署狀態通知 |

## ⚙️ 第四步：測試部署

### 4.1 第一次手動部署
1. 在 Cloudways Git 部署頁面
2. 點擊 **「Deploy Now」** 按鈕
3. 等待部署完成（通常 1-3 分鐘）
4. 檢查部署日誌是否有錯誤

### 4.2 驗證部署成功
1. **檢查檔案**：
   - SSH 到您的伺服器
   - 確認檔案在 `wp-content/plugins/maintenance-signature-system/`

2. **檢查 WordPress**：
   - 登入 WordPress 後台
   - 進入 `外掛 > 已安裝的外掛`
   - 確認看到「維修單線上簽名系統」
   - 點擊「啟用」

3. **測試功能**：
   - 在 WordPress 後台應該看到「維修單系統」選單
   - 嘗試建立一個測試維修單

## 🔄 第五步：設定自動部署

### 5.1 測試 Webhook
1. 對 GitHub 倉庫做一個小修改（例如更新 README.md）
2. 提交並推送到 main 分支
3. 檢查：
   - GitHub Actions 是否成功執行
   - Cloudways 是否自動觸發部署
   - WordPress 是否反映最新變更

### 5.2 監控部署狀態
- **GitHub Actions**: `https://github.com/jameslai-sparkofy/maintenance-signature-system/actions`
- **Cloudways 部署日誌**: 在 Git Deployment 頁面查看
- **WordPress 錯誤日誌**: 在 Cloudways 的 Log 部分查看

## 📁 部署後處理

我們已經建立了一個 `cloudways-post-deploy.sh` 腳本來處理部署後的設定。

### 啟用後處理腳本
1. 在 Cloudways Git 部署設定中
2. 找到 **「Post Deploy Script」** 選項
3. 輸入：`./cloudways-post-deploy.sh`
4. 儲存設定

這個腳本會自動：
- 設定正確的檔案權限
- 清除快取
- 記錄部署資訊
- 嘗試自動啟用插件（如果 WP-CLI 可用）

## 🎛️ 高級設定

### 環境變數設定
在 Cloudways 應用程式設定中，您可以添加環境變數：

```bash
# 開發模式（用於除錯）
WP_DEBUG=true
MSS_DEBUG_MODE=true

# 生產模式
WP_DEBUG=false
MSS_DEBUG_MODE=false
```

### 自訂部署分支
如果您想使用不同的分支進行部署：

1. **開發環境**: 使用 `develop` 分支
2. **測試環境**: 使用 `staging` 分支  
3. **生產環境**: 使用 `main` 分支

在 Cloudways Git 設定中修改 **Branch Name** 即可。

## 🛠️ 疑難排解

### 常見問題

**1. 部署失敗：「Authentication failed」**
- 檢查 GitHub Deploy Key 是否正確設定
- 確認 Deploy Key 在 GitHub 倉庫中是啟用狀態

**2. 部署成功但檔案沒更新**
- 檢查 Deploy Path 是否正確
- 確認分支名稱正確
- 查看 Cloudways 部署日誌

**3. WordPress 看不到插件**
- 確認檔案部署到正確位置：`wp-content/plugins/maintenance-signature-system/`
- 檢查檔案權限是否正確
- 查看 WordPress 錯誤日誌

**4. GitHub Actions 失敗**
- 檢查所有 Secrets 是否正確設定
- 確認 Webhook URL 有效
- 查看 Actions 日誌找出具體錯誤

### 日誌檢查

**Cloudways 部署日誌**：
```bash
# SSH 到伺服器
ssh master_xxx@your-server-ip

# 查看部署日誌
tail -f /var/log/cloudways-git-deploy.log
```

**WordPress 錯誤日誌**：
```bash
# 查看 WordPress 錯誤日誌
tail -f /home/master_xxx/applications/xxx/logs/php_error.log
```

## ✅ 部署成功檢查清單

- [ ] Cloudways Git 部署已啟用
- [ ] GitHub Deploy Key 已設定
- [ ] Webhook 已配置並測試成功
- [ ] GitHub Secrets 已設定
- [ ] 首次手動部署成功
- [ ] WordPress 中可以看到插件
- [ ] 插件已啟用並正常運作
- [ ] 自動部署測試成功
- [ ] 後處理腳本正常執行

## 🎉 完成！

現在您的維修單線上簽名系統已經設定完成，具備：

- ✅ 自動化部署流程
- ✅ 程式碼品質檢查
- ✅ 自動測試
- ✅ 部署後自動設定
- ✅ 錯誤監控和通知

每次您推送代碼到 GitHub 的 main 分支，系統就會自動部署到 Cloudways 並更新您的 WordPress 網站！