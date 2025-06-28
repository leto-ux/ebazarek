import sqlite3
import hashlib

# Returns SHA-256 hash of password
def hash_pass( password: str ) -> str:
    return hashlib.sha256( password.encode( 'utf-8' )).hexdigest()

def add_user( db_path: str, login: str, password: str, wallet: str):
    conn = sqlite3.connect( db_path )
    cursor = conn.cursor()

    pass_hash = hash_pass( password )

    try:
        cursor.execute( """
                       INSERT INTO Users (login, pass_hash, wallet)
                       VALUES (?,?,?)""",
                       ( login, pass_hash, wallet ))
        conn.commit()
        print( "User added successfully" )
    except sqlite3.IntegrityError as e:
        print( f"Error: {e}" )
    finally:
        conn.close()

if __name__ == '__main__':
    db_file = '../instance/bazabazarka.sqlite'
    log = input( "Login: " )
    passwd = input( "Password: " )
    wallet = input( "Wallet: " )

    add_user( db_file, log, passwd, wallet )
