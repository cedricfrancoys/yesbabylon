### Creation of a new branch

```
$ git branch <new-branch>
```

OR 
```
$ git branch <new-branch> <base-branch>
```
for creating a branch from another one.



### Switch to a (new) branch
```
$ git checkout <new-branch>
```


### Push changes to a (new) branch on the repository
```
git push origin <new-branch>
```


### Push and set/update upstream for current branch
```
git push --set-upstream origin <new-branch>
```



### Update default downstream

update `.git/config` file

replace section : 

```
[branch "master"]
        remote = origin
        merge = refs/heads/master
```

with :

```
[branch "2.0"]
        remote = origin
        merge = refs/heads/2.0
```



### Check which branch we're tracking
```
git branch -v
```



### Download the list of branches available on remote repository

```
git fetch
```