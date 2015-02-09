-- removes spurious support entry.
DELETE
FROM support
-- guard for rerunning this script if we ever hit id_support = 5256
WHERE id_support = 5256
  AND datetime="0000-00-00 00:00:00"
  AND status IS NULL;
