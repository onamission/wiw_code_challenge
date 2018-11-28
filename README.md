### Tim Turnquist's Code Challenge Submission

**Overview:**

It has been a while since I have used PHP. It was a lot of 
fun to get back into it. While there is a lot I would normally
do differently, the time factor kept me from doing some of those
things: like creating unit tests with `phpunit` and using a 
MVC framework like YII. It is not that those things would
normally normally take so much time, it is just that it has 
been long enough since I have used them that the re-learning curve 
made me worry that I wouldn't have time to address them properly in 
just a few days.

**Instructions:**
 1. Download this repository to a local environment
 2. From a commandline, navigate inside the root of this folder
 3. Run `docker-compose up` to create a new Docker container and image
 4. Once completed, run `docker exec -it lamp sh` to enter the image in `sh` mode
 5. You should now be at a `/www # ` prompt. Type in `php startup.php` to build the table structure and add data to the database
 6. The instance is now running and ports 80 and 3306 should be exposed
 7. Open Postman and here are some things to try:
  * Select a GET request. Here are some options to try:
      *  `localhost/index.php/shift/7` to get the seventh record
      *  `localhost/index.php/user/3` to get the third user
      *  `localhost/index.php/shift` to get a list of all shifts
      *  `localhost/index.php/user?sort=username` to get a list of all users sorted by username
      *  `localhost/index.php/shift?filter=username__eq____q__summertime__q__` to get a list of all shifts for a particular user
  * Select a PUT request. Here are a few options that work:
      *  `localhost/index.php/shift/7` 
   ```json
      {
         "shift.time_in": "2018-11-24 9:00"
      }
   ```
   ```json
      {
         "shift.time_out": "2018-11-25 7:00"
      }
   ```
  * Select a PUT request. Here are a few options that throw an exception:
      *  `localhost/index.php/shift/7` 
   ```json
      {
         "shift.time_in": "2018-11-23 9:00"
      }
   ```
   ```json
      {
         "shift.time_out": "2018-11-25 10:00"
      }
   ```
 8. Select a POST request. Here are some options that should work:
      * `localhost/index.php/user` 
   ```json
      {
         "first_name": "Toby",
         "last_name": "Mac",
         "username": "jesusfreak"
      }
   ```

      * `localhost/index.php/shift`
   ```json
      {
         "shift.time_in": "2018-11-23 9:00",
         "shift.time_in": "2018-11-23 12:00",
         "user.username": "jesusfreak"
      }
   ```
   ```json
      {
         "shift.time_in": "2018-11-23 7:00",
         "shift.time_in": "2018-11-23 9:00",
         "user.username": "jesusfreak"
      }
   ```
 9. Select a POST request. Here are some options that should fail:
      * `localhost/index.php/shift` 
   ```json
      {
         "shift.time_in": "2018-11-23 10:00",
         "shift.time_in": "2018-11-23 11:00",
         "user.username": "jesusfreak"
      }
   ```
      * `localhost/index.php/shift`
   ```json
      {
         "shift.time_in": "2018-11-23 7:00",
         "shift.time_in": "2018-11-23 10:00",
         "user.username": "jesusfreak"
      }
   ```
 10. Select a DELETE request. Here are some options that should work:
      * `localhost/index.php/shift/7` 
      * `localhost/index.php/shift`
   ```json
      {
         "user.username": "= 'jesusfreak'"
      }
   ```