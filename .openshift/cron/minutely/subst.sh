#! /bin/bash
if [ ! -f $OPENSHIFT_DATA_DIR/logs/subst ]; then
  touch $OPENSHIFT_DATA_DIR/logs/subst
fi
if [[ $(find $OPENSHIFT_DATA_DIR/logs/subst -mmin +9) ]]; then # run every 10 mins
  rm -f $OPENSHIFT_DATA_DIR/logs/subst
  touch $OPENSHIFT_DATA_DIR/logs/subst

  # Execute subst cron every 10 minutes
  cd $OPENSHIFT_REPO_DIR/crons && php subst.php > $OPENSHIFT_DATA_DIR/logs/subst
fi
