PROGRESS_FILE=/tmp/jeedom/dependancy_ArubaIot_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# ----- Log date
DATE=`date "+%Y-%m-%d %H:%M:%S"`
echo "Date : ${DATE}"

echo ""
echo "----- Update APT repository"
sudo apt-get clean
sudo apt-get update

echo ""
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
echo ""
echo "----- Change basedir : ${BASEDIR}"


echo ""
echo "----- Check if 3rparty/awss folder exists"
V_FILE="${BASEDIR}/../3rparty/awss"
V_FILE_OLD=${V_FILE}_OLD
if [ ! -d "${V_FILE}" ]; then
  echo " -> Need to create ${V_FILE}"
  mkdir ${V_FILE}
else
  echo " -> ${V_FILE} exists, move to temporary name ${V_FILE_OLD}"
  mv ${V_FILE} ${V_FILE_OLD}
  echo " -> Create new ${V_FILE}"
  mkdir ${V_FILE}
fi

# -----Change basedir
cd ${V_FILE}
BASEDIR=$( pwd )
echo ""
echo "----- Change basedir : ${BASEDIR}"

echo 10 > ${PROGRESS_FILE}

# ----- Install tools
echo ""
echo "----- Install tools with APT : composer wget unzip"
sudo apt-get install -y --no-install-recommends composer wget unzip

echo 30 > ${PROGRESS_FILE}

# ----- Download AWSS source code
# heads/main or heads/beta or tags/v1.0 ....
BRANCH_TYPE="heads"
BRANCH_NAME="main"
echo ""
echo "----- Dowload AWSS source code from github"
sudo wget https://github.com/phpconcept/aruba-ws-server/archive/refs/${BRANCH_TYPE}/${BRANCH_NAME}.zip
echo ""
echo "----- Unzip archive, install code"
sudo unzip ${BRANCH_NAME}.zip
sudo rm -f ${BRANCH_NAME}.zip
sudo mv aruba-ws-server-* websocket
sudo mv websocket/* ./
sudo rm -Rf websocket

echo 50 > ${PROGRESS_FILE}

# ----- use composer to download the additional libraries
echo ""
echo "----- Run composer to add additional third party PHP libraries"
sudo composer install

echo 95 >${PROGRESS_FILE}

echo ""
echo "----- Do some cleanup"
if [ -d "${V_FILE_OLD}" ]; then
  echo " -> Remove old folder ${V_FILE_OLD}."
  sudo rm -Rf ${V_FILE_OLD}
fi


echo 100 > ${PROGRESS_FILE}
echo ""
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}

# ----- Exit success
exit 0
