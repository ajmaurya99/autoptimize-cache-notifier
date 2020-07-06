<?php

/**
 * Notify when Autoptimise cache file size reached 50%.
 */

add_action(
  'admin_head',
  function () {
    if (class_exists('autoptimizeCache')) {
      // Retrieve the Autoptimize Cache Stats information.
      $stats = autoptimizeCache::stats();

      // Set the Max Size recommended for cache files.
      $max_size = apply_filters('autoptimize_filter_cachecheck_maxsize', 2 * 1024 * 1024 * 1024);

      // Retrieve the current Total Size of the cache.
      $bytes = $stats[1];

      // Calculate the percentage of cache used.
      $percentage = ceil($bytes / $max_size * 100);

      // check if percentage is greater than 50
      if ($percentage >= 50) {
        $send_mail    = false;
        $check_option = get_option('autoptimize_cache_notify_mail');

        if ($check_option == '' || $check_option == null) { // send mail first time
          $send_mail = true;
        } else {
          // check date difference and send mail
          $datediff = intval(time() - $check_option);
          $days     = round($datediff / (60 * 60 * 24));

          if ($percentage >= 80 && $days >= 1) { // send mail per day for more than 80%
            $send_mail = true;
          } elseif ($days >= 7) { // send mail per week for more than 50%
            $send_mail = true;
          }
        }

        if ($send_mail) {
          $mailto      = 'user@example.com';
          $mailsubject = 'Autoptimize Cache Size Increased';
          $mailbody    = 'Curently the autoptimize\'s cache size is ' . $percentage . '% which is exceeding the default size of 50%, please delete the cache.';

          // send mail and update time
          wp_mail($mailto, $mailsubject, $mailbody);

          // update current time
          update_option('autoptimize_cache_notify_mail', time());
        }
      }
    }
  },
  10,
  1
);
