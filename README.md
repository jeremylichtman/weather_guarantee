# weather_guarantee
Find out if the 680 News weather guarantee has been broken.

The Toronto news radio station, 680 News, has an ongoing contest where they guarantee the high temperature of the day, and if it is off by more than 3C, then the next day they pull a name from a set of entry tickets. The problem is that the winner then needs to call into the station (otherwise they continue adding to the pot as if nobody won), and there's no alert system (i.e. you have to be tuned into the station to know if the guarantee was broken).

The script in this repo scrapes the guaranteed high temp off of 680 News' website, and then grabs a "probable" (see below) high temp for the day off of the Environment Canada site. If the two differ by more than 3C, it outputs 'T'. Otherwise, 'F'. This script is obviously intended to be used chained to some other process (i.e. via a bash script, or some such), but could easily be modified to send an email alert instead.

Note: there's no way to cover off all of the possibilities in terms of weather conditions. If you run the script towards the end of the day, you can probably get the correct high of the day from Environment Canada, but it isn't clear whether that's the temp that 680 News is checking against. Also, temperatures can suddenly jump up and down, even in the evening, so it is completely possible that this will miss the true high of the day.

What I'm saying here is this: please do not consider this to be an infallible way of actually determining if the weather guarantee was broken. If you miss out on winning a prize as a result of relying on this, it is your problem, and in using this script (or any down the chain modification thereof), you are implicitly accepting that fact!!!
