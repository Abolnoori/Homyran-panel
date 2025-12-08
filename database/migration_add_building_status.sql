-- اضافه کردن فیلد وضعیت بنا
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS building_status VARCHAR(50) DEFAULT '' COMMENT 'وضعیت بنا (نوساز، کلید نخورده، معمولی، نیاز به تعمیر، کلنگی)';




