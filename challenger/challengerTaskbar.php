
<style>
	#taskbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 3%;
        min-height: 70px;
        background: white;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 2px 2px 8px #498428;
    }

    .logo img { 
        height: 40px; 
    }

    .nav-actions { 
        display: flex; 
        gap: 20px; 
        align-items: center; 
    }

    .icon-btn { 
        font-size: 30px; 
        cursor: pointer; 
        transition: 0.3s; 
        line-height: 1;
        display: flex;
        align-items: center;
    }

    .icon-btn:hover { 
        transform: translateY(-2px); 
    }

    .profile-pic img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 2px solid #c1d95c;
        padding: 2px;
    }

    a {
        text-decoration: none;
    }
        
    @media (max-width: 600px) {
		#taskbar {
            min-height: 60px;
			padding: 0 15px; 
		}
			
		.logo img {
			height: 35px; 
		}
			
		.nav-actions {
			gap: 10px; 
		}
			
		.icon-btn {
			font-size: 22px; 
		}
			
		.profile-pic img {
			width: 30px; 
			height: 30px;
		}
    }

	@media (min-width: 601px) and (max-width: 1200px) {
		#taskbar {
            min-height: 65px;
			padding: 0 5%; 
		}

		.nav-actions {
            gap: 15px; 
		}
			
		.icon-btn {
            font-size: 26px;
		}
	}
</style>   
        
<nav id="taskbar">
    <div class="logo">
        <a href="challengerHomePage.php"><img src="../images/Eco Points Logo.png" alt="Eco Points Logo"></a>
    </div>

    <div class="nav-actions">
        <a href="viewRecyclingInfo.php"><span class="icon-btn" title="Recycling Info">♻️</span></a>
        <a href="challenger Leaderboard.php"><span class="icon-btn" title="Leaderboard">🏆</span></a>
        <a href="userA.php"><span class="icon-btn" title ="Notification">🔔</span></a>
        <div class="profile-pic">
            <a href="challenger Profile Page.php"><img src="../images/Profile Picture.jpg" alt="Profile"></a>
        </div>
    </div>
</nav>