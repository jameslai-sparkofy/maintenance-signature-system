# Cloudways 部署路徑設定指南

## 🎯 您的情況

Cloudways 已經預設了 `public_html/` 作為部署根目錄，您只需要在後面添加 WordPress 插件的相對路徑。

## 📁 正確的部署路徑設定

在 Cloudways Git Deployment 設定中，**Deploy Path** 應該填寫：

```
wp-content/plugins/maintenance-signature-system
```

**完整路徑解釋**：
- Cloudways 固定前綴：`public_html/`
- 您需要填寫的路徑：`wp-content/plugins/maintenance-signature-system`
- 最終完整路徑：`public_html/wp-content/plugins/maintenance-signature-system`

## ⚙️ 在 Cloudways 的具體設定

### Git Deployment 設定頁面填寫：

| 欄位 | 填寫內容 |
|------|----------|
| **Git Repository URL** | `git@github.com:jameslai-sparkofy/maintenance-signature-system.git` |
| **Branch Name** | `main` |
| **Deploy Path** | `wp-content/plugins/maintenance-signature-system` |

### 設定完成後的檔案結構

部署成功後，您的檔案會出現在：

```
public_html/
├── wp-admin/
├── wp-content/
│   ├── plugins/
│   │   ├── maintenance-signature-system/  ← 您的插件會在這裡
│   │   │   ├── maintenance-signature-system.php
│   │   │   ├── includes/
│   │   │   ├── admin/
│   │   │   ├── public/
│   │   │   └── ...所有插件檔案
│   │   └── ...其他插件
│   └── themes/
├── wp-config.php
└── index.php
```

## 🔍 驗證路徑設定

### 方法一：透過 SSH 檢查
```bash
# SSH 連接到您的 Cloudways 伺服器
ssh master_xxx@your-server-ip

# 檢查插件是否部署到正確位置
ls -la public_html/wp-content/plugins/maintenance-signature-system/

# 應該會看到插件的所有檔案
```

### 方法二：透過 WordPress 後台檢查
1. 登入 WordPress 管理後台
2. 進入 `外掛 > 已安裝的外掛`
3. 應該會看到「維修單線上簽名系統」

## 🚀 完整設定步驟

### 第一步：GitHub Deploy Key
1. 複製 Cloudways 提供的 SSH Key
2. 前往 GitHub 倉庫設定：https://github.com/jameslai-sparkofy/maintenance-signature-system/settings/keys
3. 添加 Deploy Key（不勾選 write access）

### 第二步：Cloudways Git 設定
```
Repository URL: git@github.com:jameslai-sparkofy/maintenance-signature-system.git
Branch: main
Deploy Path: wp-content/plugins/maintenance-signature-system
```

### 第三步：測試部署
1. 點擊 `Deploy Now`
2. 等待部署完成
3. 檢查部署日誌

### 第四步：啟用插件
1. 進入 WordPress 後台
2. 啟用「維修單線上簽名系統」插件
3. 檢查是否出現「維修單系統」選單

## 🔧 進階設定

### 自動部署設定
如果您想要推送代碼時自動部署，可以設定 Webhook：

1. **在 Cloudways 複製 Webhook URL**
2. **在 GitHub 設定 Webhook**：
   - 前往：https://github.com/jameslai-sparkofy/maintenance-signature-system/settings/hooks
   - 添加 Webhook，使用 Cloudways 提供的 URL

### 部署後腳本
在 Cloudways 的 **Post Deploy Script** 欄位填寫：
```bash
./cloudways-post-deploy.sh
```

這會自動執行我們準備的部署後處理腳本。

## ⚠️ 常見問題

### 問題 1：「Directory already exists」
**原因**：目標目錄已存在
**解決**：Cloudways 會自動備份舊版本，正常繼續即可

### 問題 2：「Permission denied」
**原因**：權限問題
**解決**：檢查 WordPress 目錄權限，或聯繫 Cloudways 支援

### 問題 3：「Plugin not visible in WordPress」
**原因**：檔案沒有部署到正確位置
**解決**：
1. 檢查 Deploy Path 是否為 `wp-content/plugins/maintenance-signature-system`
2. 確認 WordPress 安裝在 `public_html/` 根目錄

## ✅ 設定檢查清單

- [ ] GitHub Deploy Key 已添加
- [ ] Cloudways Repository URL 設定為 SSH 格式
- [ ] Deploy Path 設定為 `wp-content/plugins/maintenance-signature-system`
- [ ] Branch 設定為 `main`
- [ ] 首次手動部署成功
- [ ] WordPress 後台可以看到插件
- [ ] 插件已啟用並正常運作

## 🎉 完成後

設定成功後，您就可以：
1. **推送代碼到 GitHub** → 自動部署到 Cloudways
2. **管理維修單** → 在 WordPress 後台使用系統
3. **生成客戶簽名連結** → 提供給客戶簽名

如果在設定過程中遇到任何問題，請提供具體的錯誤訊息，我會協助您解決！