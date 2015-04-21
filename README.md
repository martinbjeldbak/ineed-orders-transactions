# Team 5, Orders & Transactions
Xinyu Quian, Vignes Gowda, Sebastian Cheah, Sachin Bhat, Martin Madsen

## How to deploy
Note that when you deploy, only the changes in the ``web``folder will be reflected on the server, hosted here https://52.8.47.5/.

1. Add the Amazon EC2 instance as a remote repository: `git remote add production "git@52.8.47.5:/apps/ineed"`. If this doesn't work, it's because we don't have your public key added to the server's accepted keys. Let Martin know and we'll get you sorted.
2. Make your edits, commit and push to origin as normal. When you want to deploy, type `git push production master`to push the `master` branch to the `production` repository.
