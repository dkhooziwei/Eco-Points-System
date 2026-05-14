
<style>
	#taskbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 3%;
        height: 60px;
        background: white;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 2px 2px 8px #498428;
    }

    .logo img { 
        height: 40px; 
        display: block;
    }

    .nav-actions { 
        display: flex; 
        gap: 20px; 
        align-items: center; 
        height: 100%;
    }

    .icon-btn { 
        font-size: 30px; 
        cursor: pointer; 
        transition: 0.3s; 
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-btn:hover { 
        transform: translateY(-2px); 
    }

    .profile-pic img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 2px solid #c1d95c;
        object-fit: cover;;
    }

    a {
        text-decoration: none;
    }
        
    @media (max-width: 600px) {
		#taskbar {
			padding: 0 15px; 
		}
			
		.logo img {
			height: 32px; 
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
			padding: 0 5%; 
		}

		.nav-actions {
            gap: 15px; 
		}
			
		.icon-btn {
            font-size: 22px;
		}
	}
</style>   
        
<nav id="taskbar">
    <div class="logo">
        <a href="admin.php"><img src="../images/Eco Points Logo.png" alt="Eco Points Logo"></a>
    </div>

    <div class="nav-actions">
        <a href="editRecyclingInfo.php"><span class="icon-btn" title="Recycling Info">♻️</span></a>
        <a href="adminLeaderboard.php"><span class="icon-btn" title="Leaderboard">🏆</span></a>
        <a href="adminA.php"><span class="icon-btn" title ="Notification">🔔</span></a>
        <div class="profile-pic">
            <a href="admin Profile Page.php"><img src="../images/Profile Picture.jpg" alt="Profile"></a>
        </div>
    </div>
</nav>