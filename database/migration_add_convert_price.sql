-- اضافه کردن فیلد convert_price
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS convert_price DECIMAL(15,2) DEFAULT 0 COMMENT 'قیمت تبدیل';




