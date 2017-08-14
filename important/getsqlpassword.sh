#!/bin/env bash
#Fetches mysql password for various web applications.
mysqlfile=../../.sql/my.cnf
if [ `whoami` != "physics-gsc" ]
  then
    echo "You are $USER, not physics-gsc."
    exit 1
  else if [ ! -r $mysqlfile ]
    then 
      echo "You can't read $mysqlfile for some reason."
      exit 2
    else
      passwordline=`grep '^password' $mysqlfile`
      echo ${passwordline#*=}
      exit 0
    fi
  fi
