### Install
If you need to install Git: https://git-scm.com/download/win

#### From Visual Studio Code
* Load repository : File / Open folder  
* See console : View / Integrated Terminal   

#### From Command line 
```
git clone git@bitbucket.org:cwoodington/exabler.git {local_folder_name}
```

### Useful commands

#### switch to development branch
```
git checkout -b development
```
abandon current changes
```
git checkout -- .
```

#### see status of the current local version
```
$ git status
```

#### commit local changes
```
$ git commit -a -m"updated software description"
```

#### request latest changes from the server
```
git pull [origin <branc>]
```

#### update server with local changes
```
git push [origin <branc>]
```