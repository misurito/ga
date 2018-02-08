<?php 

require_once __DIR__ . '/vendor/autoload.php';

$KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

$analytics = new JC\GoogleAnalyticsClient('JC', $KEY_FILE_LOCATION);

$profileId = 65727570;

$data = $analytics->using($profileId)
                  ->between('7daysAgo', 'today')
                  ->getDirectVisits()
                  ->data();

echo ("Total Direct Visits: ".$data."\n");

echo ("Total Organic Search Sessions: ". $analytics->getOrganicSearches()->data() ."\n");

echo ("Total Users: ". $analytics->getAllUsers()->data() ."\n");

echo ("Total Contact Form Submissions: ". $analytics->getContactFormSubmissions()->data() ."\n");

echo ("Total Demo Requests: ".$analytics->getDemoRequests()->data() ."\n");

echo ("Total Upgrades to Pro: ".$analytics->getProUpgrades()->data() ."\n");

echo ("Total Ad Clicks: ".$analytics->getAdClicks()->data() ."\n");

echo ("Total CTR: ".$analytics->getCTR()->data() ."\n");

echo ("Total CPC: ".$analytics->getCPC()->data() ."\n");

echo ("Total CPM: ".$analytics->getCPM()->data() ."\n");

$data = $analytics->getDemoRequestSequenceCompletions()->data();

echo ("Segmented users: ".$data);