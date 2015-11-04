#! /bin/bash
if [ ! -f $OPENSHIFT_DATA_DIR/logs/subst ]; then
  touch $OPENSHIFT_DATA_DIR/logs/subst
fi
if [[ $(find $OPENSHIFT_DATA_DIR/last_run -mmin +4) ]]; then #run every 5 mins
  rm -f $OPENSHIFT_DATA_DIR/logs/subst
  touch $OPENSHIFT_DATA_DIR/logs/subst

  # Execute subst cron every 5 minutes
  cd $OPENSHIFT_REPO_DIR/crons && php subst.php > $OPENSHIFT_DATA_DIR/logs/subst
fi
