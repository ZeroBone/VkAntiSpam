-- messages grouped by time period
SELECT FLOOR(`date` / 3600) * 3600 AS `ts`, COUNT(1) AS `count` FROM `messages` GROUP BY `ts` ORDER BY `ts` DESC LIMIT 24;

-- messages grouped by time perion with filtered and overall count in one result

SELECT
    `A`.`ts` AS `ts`,
    `totalCount`,
    `filteredCount`
FROM
(
    SELECT
            FLOOR(`date` / 3600) * 3600 AS `ts`,
            COUNT(1) AS `totalCount`
    FROM `messages`
    GROUP BY `ts`
    ORDER BY `ts`
        DESC
    LIMIT 24
) AS `A`
INNER JOIN
(
    SELECT
            FLOOR(`date` / 3600) * 3600 AS `ts`,
            COUNT(1) AS `filteredCount`
    FROM `messages`
    WHERE
            `category` = 2 OR
            `category` = 3
    GROUP BY `ts`
    ORDER BY `ts`
        DESC
    LIMIT 24
) AS `B`
ON `A`.`ts` = `B`.`ts`