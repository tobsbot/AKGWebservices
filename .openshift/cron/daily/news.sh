#! /bin/bash
if [ ! -f $OPENSHIFT_DATA_DIR/logs/news ]; then
  touch $OPENSHIFT_DATA_DIR/logs/news
fi
if [[ $(find $OPENSHIFT_DATA_DIR/logs/news -mtime +1) ]]; then # run every 2nd day
  rm -f $OPENSHIFT_DATA_DIR/logs/news
  touch $OPENSHIFT_DATA_DIR/logs/news

  # Execute subst cron every 2nd day
  cd $OPENSHIFT_REPO_DIR/crons && php news.php > $OPENSHIFT_DATA_DIR/logs/news
fi
