<?php

/**
 * Notify when [WordPress] Autoptimise cache file size reached 50% and 80%.
 */

add_action(
  'admin_head',
  function () {
    if (class_exists('autoptimizeCacheNotify')) {
      // Retrieve the Autoptimize Cache Stats information.
      $stats = autoptimizeCacheNotify::stats();

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

          if ($percentage >= 80 && $days >= 1) { // send mail once per day if cache size > 80%
            $send_mail = true;
          } elseif ($days >= 7) { // send mail once per week if cache size > 50%
            $send_mail = true;
          }
        }

        if ($send_mail) {
          $mail_to      = 'user@example.com';
          $mail_subject = 'Autoptimize Cache Size Increased';
          $mail_body    = 'Curently the autoptimize\'s cache size is ' . $percentage . '% which is exceeding the default size of 50%, please delete the cache.';

          // send mail and update time
          wp_mail($mail_to, $mail_subject, $mail_body);

          // update current time
          update_option('autoptimize_cache_notify_mail', time());
        }
      }
    }
  },
  10,
  1
);
