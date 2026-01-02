-- Add provider_instance_id to assets
ALTER TABLE assets
ADD COLUMN provider_instance_id VARCHAR(64) DEFAULT NULL;
