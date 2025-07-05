# 維修單線上簽名系統 WordPress 插件

一個專業的 WordPress 插件，用於管理維修單並提供客戶線上簽名功能。

## 📋 功能特色

- ✅ **維修單管理**：完整的 CRUD 操作，支援維修單建立、編輯、查看、刪除
- ✅ **圖片上傳**：整合 WordPress 媒體庫，支援多張圖片上傳
- ✅ **客戶簽名**：基於 HTML5 Canvas 的電子簽名功能
- ✅ **滿意度評分**：5星評分系統
- ✅ **工務管理**：工務人員姓名管理系統
- ✅ **響應式設計**：完美支援桌面、平板、手機設備
- ✅ **資料庫整合**：使用 WordPress 標準資料庫結構
- ✅ **安全性**：遵循 WordPress 安全最佳實踐

## 🛠️ 安裝說明

### 要求
- WordPress 5.0 或更新版本
- PHP 7.4 或更新版本
- MySQL 5.6 或更新版本

### 安裝步驟

1. **下載插件**
   ```bash
   git clone https://github.com/your-username/maintenance-signature-system.git
   ```

2. **上傳到 WordPress**
   - 將整個 `maintenance-signature-system` 資料夾上傳到 `/wp-content/plugins/` 目錄
   - 或通過 WordPress 管理後台的「插件 > 新增插件 > 上傳插件」功能上傳

3. **啟用插件**
   - 在 WordPress 管理後台進入「插件」頁面
   - 找到「維修單線上簽名系統」並點擊「啟用」

4. **自動設置**
   - 插件啟用時會自動建立必要的資料庫表
   - 設定預設工務人員姓名

## 🎯 使用方法

### 管理員操作

1. **訪問插件**
   - 在 WordPress 後台選單中找到「維修單系統」

2. **建立維修單**
   - 點擊「新增維修單」
   - 填寫維修單資訊
   - 上傳相關圖片（可選）
   - 點擊「建立維修單」

3. **管理維修單**
   - 在「所有維修單」頁面查看維修單列表
   - 使用搜索功能快速找到特定維修單
   - 複製客戶簽名連結並傳送給客戶

4. **工務管理**
   - 在「系統設定」中新增或刪除工務人員姓名

### 客戶操作

1. **簽名流程**
   - 點擊收到的簽名連結
   - 查看維修單詳情和圖片
   - 填寫客戶資訊
   - 在畫布上完成簽名
   - 提供滿意度評分
   - 提交完成

2. **查看報告**
   - 簽名完成後自動跳轉到完成報告頁面
   - 可列印或分享報告

## 📁 檔案結構

```
maintenance-signature-system/
├── maintenance-signature-system.php  # 主插件檔案
├── includes/                          # 核心類別檔案
│   ├── class-database.php            # 資料庫操作
│   ├── class-admin-menu.php          # 管理後台
│   ├── class-customer-portal.php     # 客戶門戶
│   └── class-maintenance-system.php  # 主系統類別
├── admin/                             # 管理後台檔案
│   ├── css/admin-styles.css          # 管理後台樣式
│   ├── js/admin-scripts.js           # 管理後台腳本
│   └── views/                        # 管理後台視圖
│       ├── main-page.php             # 主頁面
│       ├── add-page.php              # 新增頁面
│       └── settings-page.php         # 設定頁面
├── public/                           # 前端檔案
│   ├── css/public-styles.css        # 前端樣式
│   ├── js/signature-canvas.js       # 簽名畫布
│   └── templates/                   # 前端模板
│       ├── signature-page.php       # 簽名頁面
│       └── completed-page.php       # 完成頁面
└── assets/                          # 靜態資源
```

## 💾 資料庫結構

插件會建立以下資料表：

- `wp_mss_maintenance_orders` - 維修單資料
- `wp_mss_maintenance_signatures` - 客戶簽名資料
- `wp_mss_maintenance_media` - 媒體檔案資料
- `wp_mss_constructors` - 工務人員資料

## 🔧 API 參考

### 短代碼

```php
// 顯示客戶簽名表單
[maintenance_signature_form maintenance_id="123"]
```

### 主要類別

- `MaintenanceSignatureSystem` - 主插件類別
- `MSS_Database` - 資料庫操作
- `MSS_Customer_Portal` - 客戶門戶功能

## 🚀 部署到 Cloudways

### 設置 GitHub Actions

1. **建立 `.github/workflows/deploy.yml`**：
```yaml
name: Deploy to Cloudways

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        script: |
          cd /path/to/wordpress/wp-content/plugins/
          git pull origin main
```

2. **設定 GitHub Secrets**：
   - `HOST`: Cloudways 伺服器 IP
   - `USERNAME`: SSH 使用者名稱
   - `KEY`: SSH 私鑰

### Cloudways 設定

1. **啟用 Git 支援**
2. **設定 SSH 金鑰**
3. **配置自動部署**

## 🔒 安全性

- 使用 WordPress nonce 防護
- 資料驗證和清理
- SQL 注入防護
- XSS 攻擊防護
- 檔案上傳安全檢查

## 🐛 問題回報

如果發現任何問題，請：

1. 檢查 [常見問題](https://github.com/your-username/maintenance-signature-system/wiki/FAQ)
2. 搜尋 [現有問題](https://github.com/your-username/maintenance-signature-system/issues)
3. [建立新問題](https://github.com/your-username/maintenance-signature-system/issues/new)

## 📝 更新日誌

### v1.0.0 (2025-07-05)
- 初始版本發布
- 完整的維修單管理功能
- 客戶簽名系統
- WordPress 插件架構

## 🤝 貢獻

歡迎貢獻！請閱讀 [貢獻指南](CONTRIBUTING.md) 了解詳情。

## 📄 授權

此專案採用 [GPL v2 或更新版本](LICENSE) 授權。

## 📞 支援

- 📧 Email: support@example.com
- 💬 Issues: [GitHub Issues](https://github.com/your-username/maintenance-signature-system/issues)
- 📖 文檔: [Wiki](https://github.com/your-username/maintenance-signature-system/wiki)

---

**由 ❤️ 和 WordPress 驅動**