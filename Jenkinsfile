pipeline {
  agent any
  environment{
    NODE_HOME = '/home/bae/node-v10.16.0'
    TEST_IMAGE   = 'dockerhub.wdcloud.cc/vocational-c'
    ONLINE_IMAGE = 'registry.cn-beijing.aliyuncs.com/wdcloudinf/vacational-c'
    REGISTRY     = credentials('aliyun_docker_registry')
    REGISTRY_USER = credentials('aliyun_docker_registry_user')
    REGISTRY_PWD = credentials('aliyun_docker_registry_pwd')
    DEPLOY_URL = credentials('vocational-c-k8s-deploy')
    PREVIEW_DEPLOY_URL = credentials('vocational-c-k8s-preview-deploy')
    PHP_BIN = 'php7.3'
    COMPOSER_BIN = '/usr/local/bin/composer'
  }

  parameters {
    choice(
      description: '选择要上线的环境',
      name: 'environment',
      choices: ['test','online', 'preview']
    )
    string(
        description: '镜像tag',
        name: 'imagetag',
        defaultValue: '1.0.0',
    )
  }

  stages {
    stage('Prepare') {
        steps {
                echo "build_tag: ${params.environment}"
                sh '$PHP_BIN $COMPOSER_BIN update --no-dev'
                sh '$PHP_BIN $COMPOSER_BIN dumpauto'
                sh '$PHP_BIN artisan annotation:clear'
                sh '$PHP_BIN artisan annotation:cache'
             }
    }

    stage('build_test') {
            when {
               expression { params.environment == 'test' }
            }

            steps {
                sh "cp -f .env.test .env"
                sh "docker build --build-arg env=${environment} -t ${env.TEST_IMAGE}:latest  -f deploy/Dockerfile ."
            }
    }

    stage('build_online') {
        when {
           expression { params.environment == 'online' }
        }
        steps {
            sh "cp -f .env.online .env"
            sh "docker build --build-arg env=${environment} -t ${env.ONLINE_IMAGE}:${imagetag}  -f deploy/Dockerfile ."
            sh "docker tag ${env.ONLINE_IMAGE}:${imagetag} ${env.ONLINE_IMAGE}:latest"
        }
    }

    stage('build_preview') {
            when {
               expression { params.environment == 'preview' }
            }
            steps {
                sh "cp -f .env.online .env"
                sh "docker build --build-arg env=online -t ${env.ONLINE_IMAGE}:preview  -f deploy/Dockerfile ."
            }
        }

    stage('push tag') {
        stages {
            stage(confirm) {
                steps {
                    timeout(time: 5, unit: 'MINUTES') {
                        input message: "确认push？", ok: 'push'
                    }
                }
            }

            stage('test_tag') {
                when {
                    expression { params.environment == 'test' }
                }

                steps {
                    sh "docker push ${env.TEST_IMAGE}:latest"
                    sh "curl http://proxy.k8s.wdcloud.cc/api/deploy/aitiaozhan/vocational-c" //重新部署测试k8s
                }
            }

            stage('online_tag') {
                when {
                    expression { params.environment == 'online' }
                }
                steps {
                    sh "docker login -u=${env.REGISTRY_USER} -p=${env.REGISTRY_PWD} ${env.REGISTRY}"
                    sh "docker push ${env.ONLINE_IMAGE}:${imagetag}"
                    sh "docker push ${env.ONLINE_IMAGE}:latest"
                    sh "docker logout ${env.REGISTRY}"
                }
            }

            stage('preview_tag') {
                when {
                    expression { params.environment == 'preview' }
                }
                steps {
                    sh "docker login -u=${env.REGISTRY_USER} -p=${env.REGISTRY_PWD} ${env.REGISTRY}"
                    sh "docker push ${env.ONLINE_IMAGE}:preview"
                    sh "docker logout ${env.REGISTRY}"
                }
            }
        }
    }

    stage('k8s deploy') {
        when {
          expression { params.environment == 'online' }
        }
        steps {
          timeout(time: 5, unit: 'MINUTES') {
            input message: "是否部署K8s？", ok: '部署'
          }
          echo "部署"
          sh "curl ${env.DEPLOY_URL}"
        }
    }

    stage('k8s preview deploy') {
        when {
          expression { params.environment == 'preview' }
        }
        steps {
          timeout(time: 5, unit: 'MINUTES') {
            input message: "是否部署K8s？", ok: '部署'
          }
          echo "部署"
          sh "curl ${env.PREVIEW_DEPLOY_URL}"
        }
    }
  }

  post {
        always {
            script {
                try{
                  sh "docker rmi ${env.ONLINE_IMAGE}:${imagetag}  ${env.ONLINE_IMAGE}:latest  ${env.ONLINE_IMAGE}:preview ${env.TEST_IMAGE}:latest"
                } catch(e){
                  print e
                }
            }
        }
        success {
            echo 'I succeeeded!'
        }
        unstable {
            echo 'I am unstable :/'
        }
    }
}
