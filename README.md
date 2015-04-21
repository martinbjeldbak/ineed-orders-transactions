# Team 5, Orders & Transactions
Xinyu Quian, Vignes Gowda, Sebastian Cheah, Sachin Bhat, Martin Madsen

## Getting started

1. Clone this repository to your local machine `git clone git@github.com:martinbmadsen/ineed-orders-transactions.git`. You will need to log in via the command line if you have not set up public key authentication with GitHub
2. Commit changes, then push to the `master` branch `git push origin master`, or `git push master`
3. Voilá!

## How to deploy
Note that when you deploy, only the changes in the ``web``folder will be reflected on the server, hosted here https://52.8.47.5/.

1. Add the Amazon EC2 instance as a remote repository `git remote add production "git@52.8.47.5:/apps/ineed"`. If this doesn't work, it's because we don't have your public key added to the server's accepted keys. Let Martin know and we'll get you sorted.
2. Make your edits, commit and push to origin as normal. When you want to deploy, type `git push production master`to push the `master` branch to the `production` repository.
