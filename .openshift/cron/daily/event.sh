#! /bin/bash
  rm -f $OPENSHIFT_DATA_DIR/logs/event
  touch $OPENSHIFT_DATA_DIR/logs/event

  # Execute subst cron every day
  cd $OPENSHIFT_REPO_DIR/crons && php event.php > $OPENSHIFT_DATA_DIR/logs/event
