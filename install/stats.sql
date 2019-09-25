-- messages grouped by time period
SELECT FLOOR(`date` / 3600) * 3600 AS `ts`, COUNT(1) AS `count` FROM `messages` GROUP BY `ts` ORDER BY `ts` DESC LIMIT 24;