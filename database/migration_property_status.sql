-- اضافه کردن فیلدهای وضعیت ملک
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS property_status VARCHAR(20) DEFAULT 'empty' COMMENT 'وضعیت ملک (tenant=مستاجر, empty=خالی)',
ADD COLUMN IF NOT EXISTS vacancy_months INT DEFAULT 0 COMMENT 'تعداد ماه تا خالی شدن (فقط برای مستاجر)';

