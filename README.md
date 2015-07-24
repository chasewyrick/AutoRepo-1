# AutoRepo
Cydia repositories - simpler than ever

# What is this?
AutoRepo is - or rather: will be - a PHP script that automatically generates an up-to-date Packages(.gz)-file whenever a new debian package is uploaded to a given directory. 
That means, whoever hosts the repository does not have to do anything anymore, except for placing their .deb file on their webspace. 

# Motivation
Earlier this day, I read this on Reddit
https://www.reddit.com/r/jailbreakdevelopers/comments/3ecucb/discussion_cydia_repo_creator_beta/
and just like /u/superchilpil I thought, nobody needs an application to create a repository if they are able to create something worth being distributed. However, the OP has a point there, saying it is a nifty thing to just have to drag and drop. 
Considering he revealed to us that it took him 100 hours of work to create his tool and that he wanted to make his tool paid, I felt the urge to compete with him and develop my own concept for an easy "drag-and-drop repository" which I am currently implementing. 

# State
Up to now, I quickly implemented a little function to get the contents of the "control"-file of debian packages, 
I didn't really care about good error handling yet. 

# License
Check the LICENSE-File. 
