<?php
/**
 * Yom Tov–aware scheduling for the weekly pot.
 *
 * Rule: a pot never ENDS in a week that contains a Yom Tov. When the cron
 * opens a new weekly, the proposed draw date (next Thursday 20:00) keeps
 * moving one week later until the final 7 days before the draw are clean.
 * In the common case (e.g. Shavuos midweek) that produces one 14-day
 * "Special Pot"; holidays that span two weeks (Pesach's two legs, the
 * Tishrei season) extend further so the draw never lands on/around Yom Tov.
 *
 * Holiday dates come from PHP's built-in Hebrew calendar (calendar
 * extension) — no external service, correct forever.
 *
 * Tracked: Rosh Hashana, Yom Kippur, Succos (incl. Shmini Atzeres /
 * Simchas Torah), Chanukah, Purim, Pesach, Shavuos. Diaspora lengths.
 */

/** All tracked holiday days of one Hebrew year, as ['Y-m-d' => name]. */
function goral_yomtov_days_for_hebrew_year($hy) {
	$days = [];
	$add = function ($jd, $name) use (&$days) {
		list($m, $d, $y) = explode('/', jdtogregorian($jd));
		$days[sprintf('%04d-%02d-%02d', $y, $m, $d)] = $name;
	};

	// PHP Hebrew months: 1=Tishrei, 3=Kislev, 7=Adar (Adar II in leap years),
	// 8=Nisan, 10=Sivan.
	$add(jewishtojd(1, 1, $hy), 'Rosh Hashana');
	$add(jewishtojd(1, 2, $hy), 'Rosh Hashana');
	$add(jewishtojd(1, 10, $hy), 'Yom Kippur');
	for ($d = 15; $d <= 23; $d++) {
		$add(jewishtojd(1, $d, $hy), 'Succos');
	}
	$chanukah = jewishtojd(3, 25, $hy);
	for ($i = 0; $i < 8; $i++) {
		$add($chanukah + $i, 'Chanukah');
	}
	$add(jewishtojd(7, 14, $hy), 'Purim');
	for ($d = 15; $d <= 22; $d++) {
		$add(jewishtojd(8, $d, $hy), 'Pesach');
	}
	$add(jewishtojd(10, 6, $hy), 'Shavuos');
	$add(jewishtojd(10, 7, $hy), 'Shavuos');

	return $days;
}

/** Holiday map covering the Hebrew year at $ts and the following one. */
function goral_yomtov_map_around($ts) {
	$jd = gregoriantojd((int) date('n', $ts), (int) date('j', $ts), (int) date('Y', $ts));
	$hy = (int) explode('/', jdtojewish($jd))[2];
	return goral_yomtov_days_for_hebrew_year($hy) + goral_yomtov_days_for_hebrew_year($hy + 1);
}

/**
 * Given a pot's start timestamp (a Thursday 20:30), return
 * [endTimestamp, holidayNames[]]. End = the first Thursday 20:00 whose
 * preceding 7 calendar days (Friday..draw Thursday) contain no Yom Tov.
 */
function goral_weekly_end_date($startTs) {
	$end = strtotime(date('Y-m-d', strtotime('+7 days', $startTs)) . ' 20:00');
	$names = [];

	if (!function_exists('jewishtojd')) {
		error_log('[YomTov] PHP calendar extension missing — defaulting to 7-day pots.');
		return [$end, $names];
	}

	$map = goral_yomtov_map_around($startTs);

	// 6 extensions = up to a 49-day pot, far beyond the worst real case
	// (the Tishrei season needs 4). Pure safety bound.
	for ($guard = 0; $guard < 6; $guard++) {
		$hit = false;
		for ($i = 6; $i >= 0; $i--) {
			$day = date('Y-m-d', strtotime("-{$i} days", $end));
			if (isset($map[$day])) {
				$hit = true;
				if (!in_array($map[$day], $names, true)) {
					$names[] = $map[$day];
				}
			}
		}
		if (!$hit) {
			break;
		}
		$end = strtotime('+7 days', $end);
	}

	return [$end, $names];
}

/** Campaign name for a pot: "Weeks Pot", "<Holiday> Special Pot", or seasonal. */
function goral_weekly_campaign_name(array $yomtovNames) {
	if (empty($yomtovNames)) {
		return 'The Goral Weekly Pot';
	}
	if (count($yomtovNames) === 1) {
		return $yomtovNames[0] . ' Special Pot';
	}
	return 'Yom Tov Special Pot';
}
?>
