-- Add version column to provider_instances table
ALTER TABLE provider_instances ADD COLUMN version VARCHAR(20) DEFAULT '1.0';