### Tim Turnquist's Code Challenge Submission

**Overview:**
It has been a while since I have used PHP. It was a lot of 
fun to get back into it. While there is a lot I would normally
do differently, the time factor kept me from doing some of those
things: like creating unit tests with `phpunit` and using a 
MVC framework like YII. It is not that those things take 
longer, normally, it is just that it has been long enough that
there is a re-learning curve that I don't have time to address 
in a few days.

**Instructions**
1. Download this repository to a local environment
2. From a commandline, navigate inside the root of this folder
3. Run `docker-compose up` to create a new Docker container and image
4. Once completed, run `docker exec -it lamp sh` to enter the image in `sh` mode
5. You should now be at a `/www # ` prompt. Type in `php startup.php` to build the table structure and add data to the database
6. The instance is now running and ports 80 and 3306 should be exposed
7. Open Postman:
  * Select a GET method and enter `localhost/index.php/shift/7` into the request area, click "SEND" to get the seventh record
  * Select a GET method and enter `localhost/index.php/user/3` into the request area, click "SEND" to get the third user
  * Select a GET method and enter `localhost/index.php/shift` into the request area, click "SEND" to get a list of all shifts
  * Select a GET method and enter `localhost/index.php/user` into the request area, click "SEND" to get a list of all users
  * Select a PUT method and enter `localhost/index.php/shift/7` into the request area, and in the 'body' section enter 
  ```json
      {
         "SET":{
            "shift.time_in":"2018-11-24 9:00",
            "shift.time_out": "2018-11-24 11:00"
         }
      }
```

   then click "SEND" to change the start time

  * Select a PUT method and enter `localhost/index.php/shift/7` into the request area, click "SEND" to change the end time