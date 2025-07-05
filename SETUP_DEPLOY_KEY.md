# 設定 Cloudways Deploy Key 指南

## 🔑 您收到的 SSH Key 用途

Cloudways 給您的 SSH Key 是一個 **Deploy Key**，它讓 Cloudways 伺服器能夠：
- 從您的 GitHub 倉庫讀取代碼
- 自動拉取最新的代碼更新
- 執行自動部署

## 📋 立即執行步驟

### 第一步：複製 Cloudways 提供的 SSH Key

1. 在 Cloudways Git Deployment 頁面
2. 找到並複製完整的 SSH Public Key
3. 通常格式類似：`ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...`

### 第二步：在 GitHub 添加 Deploy Key

1. **前往您的 GitHub 倉庫**：
   https://github.com/jameslai-sparkofy/maintenance-signature-system

2. **進入設定頁面**：
   - 點擊 `Settings` 標籤
   - 在左側選單選擇 `Deploy keys`

3. **添加 Deploy Key**：
   - 點擊 `Add deploy key` 按鈕
   - 填寫資訊：
     - **Title**: `Cloudways Auto Deploy`
     - **Key**: 貼上 Cloudways 提供的完整 SSH Key
     - **Allow write access**: **不要勾選**（只需要讀取權限）
   - 點擊 `Add key`

### 第三步：在 Cloudways 設定部署參數

回到 Cloudways Git Deployment 頁面，填寫：

1. **Git Repository URL**: 
   ```
   git@github.com:jameslai-sparkofy/maintenance-signature-system.git
   ```
   （注意：使用 SSH 格式，不是 HTTPS）

2. **Branch Name**: 
   ```
   main
   ```

3. **Deploy Path**: 
   ```
   wp-content/plugins/maintenance-signature-system
   ```

### 第四步：測試連接

1. 在 Cloudways 頁面點擊 `Verify Repository` 或 `Test Connection`
2. 如果設定正確，應該會顯示 ✅ 連接成功
3. 如果失敗，檢查：
   - SSH Key 是否完整複製
   - GitHub Repository URL 格式是否正確
   - Deploy Key 是否已在 GitHub 啟用

### 第五步：執行首次部署

1. 點擊 `Deploy Now` 按鈕
2. 等待部署完成（通常 1-3 分鐘）
3. 查看部署日誌確認無錯誤

## 🔍 驗證部署成功

### 檢查 WordPress 後台
1. 登入您的 WordPress 管理後台
2. 進入 `外掛 > 已安裝的外掛`
3. 應該會看到「維修單線上簽名系統」
4. 點擊「啟用」

### 檢查插件功能
1. 啟用後，在 WordPress 後台左側選單應該會出現「維修單系統」
2. 點擊進入，測試基本功能

## 🚨 如果遇到問題

### Deploy Key 設定問題
**錯誤訊息**: "Permission denied" 或 "Authentication failed"

**解決方法**:
1. 確認 SSH Key 完全複製（包含開頭的 `ssh-rsa` 和結尾）
2. 檢查 GitHub Repository URL 使用 SSH 格式：`git@github.com:username/repo.git`
3. 確認 Deploy Key 在 GitHub 中是 "Active" 狀態

### 部署路徑問題
**錯誤訊息**: "Directory not found" 或檔案沒有出現在正確位置

**解決方法**:
1. 確認 Deploy Path 正確：`wp-content/plugins/maintenance-signature-system`
2. 檢查 WordPress 安裝路徑是否正確

### 權限問題
**錯誤訊息**: "Permission denied" 在部署過程中

**解決方法**:
1. 檢查 WordPress 目錄權限
2. 確認 Cloudways 用戶有寫入權限

## 🎯 下一步：設定自動部署

Deploy Key 設定成功後，您可以：

1. **設定 Webhook** 實現推送代碼時自動部署
2. **設定 Post-Deploy Script** 自動處理部署後任務
3. **測試完整工作流程** 從代碼推送到自動部署

## 📞 需要協助？

如果在設定過程中遇到問題，請提供：
1. 具體的錯誤訊息
2. Cloudways 部署日誌
3. 您當前的設定截圖

我可以幫您診斷和解決問題！