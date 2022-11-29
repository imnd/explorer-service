pipeline {
    agent any

    stages {
        stage('Build') {
            steps {
			   sh "ssh -o StrictHostKeyChecking=no -i /home/dogovor24_rsa root@10.133.51.179 'cd /var/www/explorer-service && git checkout -- composer.lock && git pull && bash deploy.sh'"
            }
        }
    }
}
