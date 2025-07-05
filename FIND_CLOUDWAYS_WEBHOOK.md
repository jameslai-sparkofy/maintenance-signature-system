# 如何找到 Cloudways Webhook URL

## 📍 在 Cloudways 控制台尋找 Webhook URL

### 方法一：Git Deployment 頁面
1. **登入 Cloudways 控制台**
   - 前往 https://platform.cloudways.com
   - 登入您的帳號

2. **選擇您的應用程式**
   - 點擊您的 WordPress 應用程式

3. **進入 Git Deployment**
   - 在左側選單點擊 **"Git Deployment"**
   - 或在應用程式管理頁面找到 Git 相關設定

4. **尋找 Webhook URL**
   - 在 Git Deployment 頁面中尋找 **"Webhook URL"** 或 **"Git Webhook"**
   - 通常會顯示類似：
     ```
     https://api.cloudways.com/api/v1/git-deploy/webhook/xxxxxxxx
     ```
   - 點擊 **"Copy"** 按鈕複製 URL

### 方法二：Application Management
1. **進入應用程式管理**
   - 選擇您的 WordPress 應用程式
   - 點擊 **"Application Management"**

2. **查看 Git 設定**
   - 尋找 **"Git"** 或 **"Deployment"** 相關選項
   - Webhook URL 可能在這個區域顯示

## 📋 Webhook URL 的格式

Cloudways Webhook URL 通常格式如下：
```
https://api.cloudways.com/api/v1/git-deploy/webhook/[唯一識別碼]
```

範例：
```
https://api.cloudways.com/api/v1/git-deploy/webhook/abc123def456
```

## 🔍 如果找不到 Webhook URL

### 檢查 Git 是否完全啟用
1. 確認您已經完成 Git Deployment 的所有設定：
   - ✅ Repository URL
   - ✅ Branch Name  
   - ✅ Deploy Path
   - ✅ Deploy Key 已添加到 GitHub

2. **儲存設定**後，Webhook URL 才會生成

### 重新整理頁面
有時候需要重新整理 Cloudways 頁面才會顯示 Webhook URL。

### 聯繫 Cloudways 支援
如果仍然找不到，可以：
1. 使用 Cloudways 線上客服
2. 提交支援票券詢問如何獲取 Webhook URL

## 🔗 設定 GitHub Webhook

找到 Cloudways Webhook URL 後：

1. **前往 GitHub 倉庫**：
   https://github.com/jameslai-sparkofy/maintenance-signature-system

2. **進入 Webhook 設定**：
   - 點擊 `Settings`
   - 左側選單選擇 `Webhooks`
   - 點擊 `Add webhook`

3. **填寫 Webhook 資訊**：
   - **Payload URL**: 貼上 Cloudways Webhook URL
   - **Content type**: 選擇 `application/json`
   - **Secret**: 留空
   - **Which events**: 選擇 `Just the push event`
   - **Active**: 確保勾選
   - 點擊 `Add webhook`

## 🧪 測試 Webhook

設定完成後測試：

1. **對 GitHub 倉庫做小修改**：
   ```bash
   # 在本地修改任何檔案，例如 README.md
   git add .
   git commit -m "Test webhook deployment"
   git push origin main
   ```

2. **檢查自動部署**：
   - 在 Cloudways Git Deployment 頁面查看是否自動觸發部署
   - 在 GitHub 的 Webhooks 頁面查看是否成功送出請求

## 📱 截圖位置參考

在 Cloudways 控制台中，Webhook URL 通常會出現在：

```
Applications → [您的應用程式] → Git Deployment → Webhook URL
```

或

```
Applications → [您的應用程式] → Application Management → Git → Webhook
```

## 🆘 如果還是找不到

請提供：
1. 您的 Cloudways 控制台截圖（Git Deployment 頁面）
2. 您看到的選項和按鈕
3. 任何錯誤訊息

我可以幫您更具體地指出 Webhook URL 的位置！