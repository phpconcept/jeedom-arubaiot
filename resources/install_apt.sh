PROGRESS_FILE=/tmp/dependancy_ArubaIot_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
sudo apt-get update

echo "----- Check if 3rparty folder exists"
V_FILE="${BASEDIR}/../3rparty"
if [ ! -d "${V_FILE}" ]; then
  echo " -> Need to create ${V_FILE}"
  mkdir ${V_FILE}
else
  echo " -> Ok, ${V_FILE} exists"
fi

# ----- Change basedir
cd ${V_FILE}
BASEDIR=$( pwd )
echo "----- Change basedir : ${BASEDIR}"


echo "----- Check if 3rparty/awss folder exists"
V_FILE="${BASEDIR}/../3rparty/awss_test"
V_FILE_OLD=${V_FILE}_OLD
if [ ! -d "${V_FILE}" ]; then
  echo " -> Need to create ${V_FILE}"
  mkdir ${V_FILE}
else
  echo " -> ${V_FILE} exists, move to temporary new name"
  mv ${V_FILE} ${V_FILE_OLD}
  echo " -> Create new ${V_FILE}"
  mkdir ${V_FILE}
fi

# -----Change basedir
cd ${V_FILE}
BASEDIR=$( pwd )
echo "----- Change basedir : ${BASEDIR}"

echo 10 > ${PROGRESS_FILE}

# ----- Install tools
# apt-get -y install composer wget unzip

echo 30 > ${PROGRESS_FILE}

# ----- Download AWSS source code
# heads/main or heads/beta or tags/v1.0 ....
BRANCH_TYPE="heads"
BRANCH_NAME="beta"
wget https://github.com/phpconcept/aruba-ws-server/archive/refs/${BRANCH_TYPE}/${BRANCH_NAME}.zip
unzip ${BRANCH_NAME}.zip
rm -f ${BRANCH_NAME}.zip
mv aruba-ws-server-* websocket
mv websocket/* ./
rm -Rf websocket

echo 50 > ${PROGRESS_FILE}

# ----- use composer to download the additional libraries
# composer install


echo 95 >${PROGRESS_FILE}

echo "----- Do some cleanup"
if [ -d "${V_FILE_OLD}" ]; then
  echo " -> Remove old folder ${V_FILE_OLD}."
  rm -Rf ${V_FILE_OLD}
fi


echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}

