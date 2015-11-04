#! /bin/bash
if [ ! -f $OPENSHIFT_DATA_DIR/subst/last_run ]; then
  touch $OPENSHIFT_DATA_DIR/subst/last_run
fi
if [[ $(find $OPENSHIFT_DATA_DIR/last_run -mmin +4) ]]; then #run every 5 mins
  rm -f $OPENSHIFT_DATA_DIR/subst/last_run
  touch $OPENSHIFT_DATA_DIR/subst/last_run

  # Execute subst cron every 5 minutes
  cd $OPENSHIFT_REPO_DIR/crons && php subst.php >> $OPENSHIFT_DATA_DIR/subst/log
fi
