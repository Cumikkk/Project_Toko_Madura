-- Add biaya_langganan_outlet column to investor table
ALTER TABLE `investor` ADD COLUMN `biaya_langganan_outlet` DECIMAL(15,2) NOT NULL DEFAULT 100000.00 AFTER `persen_bagian_investor`;
