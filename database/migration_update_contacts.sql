-- به‌روزرسانی ساختار تماس و حذف فیلدهای قدیمی
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS contacts TEXT COMMENT 'اطلاعات تماس مالک/مستاجر به صورت JSON',
ADD COLUMN IF NOT EXISTS direction VARCHAR(20) DEFAULT '' COMMENT 'جهت بنا (شمالی/جنوبی)';

-- حذف فیلدهای قدیمی (اختیاری - در صورت نیاز می‌توانید این خطوط را کامنت کنید)
-- ALTER TABLE properties DROP COLUMN IF EXISTS phone;
-- ALTER TABLE properties DROP COLUMN IF EXISTS mobile;
-- ALTER TABLE properties DROP COLUMN IF EXISTS owner_phone;
-- ALTER TABLE properties DROP COLUMN IF EXISTS tenant_phone;
-- ALTER TABLE properties DROP COLUMN IF EXISTS owner_name;

-- تغییر city به اختیاری (اگر قبلاً required بوده)
ALTER TABLE properties MODIFY COLUMN city VARCHAR(100) DEFAULT '' COMMENT 'شهر (اختیاری)';


