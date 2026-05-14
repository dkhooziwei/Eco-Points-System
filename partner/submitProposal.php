<?php
require("../session.php");
include("../conn.php");

if($_SESSION["role"] !== "Partner"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

$partner_id = $_SESSION['mySession'];

/*Fetch partner info from user table*/ 
$partner_query = mysqli_query($con, "SELECT * FROM user WHERE user_id = '$partner_id'");
$partner_data = mysqli_fetch_assoc($partner_query);
$partner_name = $partner_data['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner - Submit Proposal</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>

        :root {
            --light-lime: #eaef9d;
            --lime: #c1d95c;
            --soft-green: #80b155;
            --mid-green: #498428;
            --dark-green: #336a29;
        }

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;      
        }

        /*Submit Proposal Container style*/
        .submit-proposal-container{
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-green);
            padding-top: 50px;
            padding-left: 26%;
            margin-bottom: 40px;
        }

        p{
            font-size: 1rem;
            color: var(--dark-green);
            font-weight: 300;
        }

        /*Submit Proposal form style*/
        .submit-proposal-form{
            background-color: white;
            width: 100%;
            height: auto;
            max-width: 800px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            align-items: center;
            display: flex;
            margin: auto;
            margin-bottom: 30px;
            padding-top: 15px;
            justify-content: center;
        }

        .proposal-form .form-group{
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
        }

        .proposal-form .form-group label{
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--mid-green);
        }

        .proposal-form .form-group input[type="text"],
        .proposal-form .form-group textarea{
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
        }

        .file-upload{
            border: 2px dashed var(--lime);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
            color: #555;
        }

        .file-upload:hover {
            background-color: var(--light-lime);
            border-color: var(--mid-green);
        }

        .file-upload .upload-icon svg{
            height: 50px;
            fill: var(--mid-green);
        }

        .file-upload .file-text span{
            font-weight: 600;
            color: var(--dark-green);
            font-size: 0.9rem;
        }

        .file-upload input{
            display: none;
        }

        .file-input-type{
            display: block;
            margin: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            color:var(--soft-green);
            font-style: italic;
        }

        .btn-group{
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-cancel, .btn-submit{
            padding: 13px 120px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-cancel{
            background-color: #ccc;
            color: #333;
        }

        .btn-cancel : hover{
            background-color: #bbb;
        }

        .btn-submit{
            background-color: var(--mid-green);
            color: white;
        }

        .btn-submit:hover{
            background-color: var(--dark-green);
        }

        /*Toast msg*/
        #toast-container{
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: none; 
            background-color: white; 
        }

        .toast{
            background-color: var(--mid-green) 
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn{
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        /*mobile view */
        @media (max-width: 600px) {
            .submit-proposal-container {
                padding-left: 8%;
                text-align: left;
                padding-right: 5%;
                padding-top: 30px;
            }

            .submit-proposal-container h2 {
                font-size: 1.8rem;
            }

            .submit-proposal-form {
                max-width: 80%;
                padding: 20px;
                margin-bottom: 20px;
            }

            .btn-group {
                flex-direction: column-reverse; 
                gap: 10px;
            }

            .btn-cancel, .btn-submit {
                width: 100%;
                padding: 13px 0;
            }

            .file-upload {
                padding: 20px 10px; 
        }

        /*tablet view */
        @media (min-width: 601px) and (max-width: 1200px) {
            .submit-proposal-container {
                padding-top: 40px;
                padding-left: 8%;
                text-align: left;
            }

            .submit-proposal-form {
                max-width: 80%; 
                padding: 30px;
                margin: 0 auto 30px;
            }

            .btn-cancel, .btn-submit {
                width: 100%;
                padding: 13px 60px; 
            }
        }

    </style>
</head>
<body>

<?php 
include("partnerTaskbar.php")
?>

<main>
    <div class="submit-proposal-container">
        <h2>Submit Proposal</h2>
        <p>Submit your proposal for a new recycling challenge or environmental event</p>
    </div>
    
    <div class="submit-proposal-form">
        <form action="submitProposalProcess.php" method="POST" class="proposal-form" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label>Proposal Title *</label>
                <input type="text" name="title" placeholder="Enter a clear and concise title." required>
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" placeholder="Provide a detailed description including objectives and outcomes." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Upload Proposal Document *</label>
                <label for="file-input" class="file-upload">
                    <div class="upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="" viewBox="0 0 24 24">
                            <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                            <g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g>
                            <g id="SVGRepo_iconCarrier"> <path fill="" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" clip-rule="evenodd" fill-rule="evenodd"></path> </g>
                        </svg>
                    </div>
                    <div class="file-text">
                        <span id="file-name-display">Drag and drop your file here or <strong>Browse files</strong></span>
                    </div>
                    <input type="file" name="proposal_file" id="file-input" accept=".pdf,.doc,.docx" required>
                </label>
                <span class="file-input-type">Supported formats: PDF, DOC, DOCX (Max size: 5MB)</span>
            </div>

            <div class="btn-group">
                <button type="button" class="btn-cancel" onclick="resetForm()">Cancel</button>
                <button type="submit" name="submit_proposal" class="btn-submit">Submit Proposal</button>
            </div>
        </form>
    </div>

    <div id= "toast-container">
        <div class= "toast">
            <span>✅</span>
            <span id= "toast-message"></span>
        </div>
    </div>
</main>

<script>
    const fileInput= document.getElementById('file-input');
    const fileNameDisplay = document.getElementById('file-name-display');

    fileInput.addEventListener('change', function(){
        if(this.files && this.files.length > 0){
            fileNameDisplay.innerText = "Selected file: " + this.files[0].name;
        }
    });

    function resetForm(){
        document.querySelector('form').reset();
        window.location.href = 'submitProposal.php';
    }
</script>

<?php if (isset($_SESSION['status']) && $_SESSION['status'] == 'success'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast-container');
        const message = document.getElementById('toast-message');
        
        
        message.innerText = "<?php echo $_SESSION['message']; ?>'";
        
        
        toast.style.display = 'block';
         
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000);
    });
</script>
<?php 
    unset($_SESSION['status']); 
    unset($_SESSION['message']);
endif; 
?>
</body>
</html>