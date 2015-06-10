import java.io.*;
import java.sql.*;
import java.util.*;

import org.jaudiotagger.tag.*;
import org.jaudiotagger.tag.flac.*;
import org.jaudiotagger.tag.vorbiscomment.*;
import org.jaudiotagger.tag.mp4.*;
import org.jaudiotagger.audio.*;
import org.apache.commons.codec.digest.DigestUtils;

import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;

public class MusicUpdater {
	static File[] drive_listing = new File[10000];
	
	static int file_count = 0;
	static int audio_count = 0;
	static int video_count = 0;
	static int new_count = 0;
	static int new_audio_count = 0;
	static int new_video_count = 0;
	static int error_count = 0;
	static int md5_error_count = 0;
	static int missing_file_count = 0;
	
	static String host = "localhost";						// -h
	static String port = "3306";							// -P
	static String database = "sarah";						// -d
	static String username = "douglas";						// -u
	static String password = "fargo";						// -p
	
	static String	fileRoot;
	static String	serverStartPath;
	static String	webStartPath;
	static String	flacTranscodePath;
	static String[] audio;
	static String[] video;
	
	static boolean isVerbose = false;
	static boolean isDebug = false;
	
	public static void main (String[] args) {
		java.util.Date date = new java.util.Date();
		String start_timestamp = (new Timestamp (date.getTime())).toString();

//		Logger.getLogger("org.jaudiotagger").setLevel(Level.OFF);

		// read global sarah config (for database details), Do this before parsing arguments so arguments can override global config settings
		JSONParser jsonParser = new JSONParser();
        try {     
            Object obj = jsonParser.parse(new FileReader("../../../../config.json"));

            JSONObject jsonObject =  (JSONObject) obj;

            host = (String) jsonObject.get("DB_ADDRESS");
            port = (String) jsonObject.get("DB_PORT");
            database = (String) jsonObject.get("DB_NAME");
            username = (String) jsonObject.get("DB_USER");
            password = (String) jsonObject.get("DB_PASS");
        } catch (Exception e) {
            System.out.println ("ERROR: parsing global config.json");
        }

		// parse arguments
		for (int i = 0; i < args.length; i++) {
			String arg = args[i];
			String value = "";
			char key = ' ';
			
			if (arg.startsWith ("-") && arg.length() >= 2) {
				key = arg.charAt(1);
				
				// no value for these flags, so we dont want to define the value
				if ( !(arg.equals("-v") || arg.equals("-t")) ){
					value = args[++i];
				}
			}

			switch (key) {
				case 'h': 	host = value;
							break;
				case 'P':	port = value;
							break;
				case 'd':	database = value;
							break;
				case 'u':	username = value;
							break;
				case 'p':	password = value;
							break;
				case 'r':	fileRoot = value.replace("\\", "/");
							break;
				case 's':	serverStartPath = value.replace("\\", "/");
							break;
				case 'H':	webStartPath = value;
							break;
				case 'v':	isVerbose = true;
							break;
				case 't':	isDebug = true;
							break;
				case '?':	showHelp();
							break;
				default:	System.out.println ("See -? for help");
			};
		}
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			Connection conn = null;
			Statement stmt = null;
			ResultSet rs;
			
			conn = DriverManager.getConnection("jdbc:mysql://" + host + ":" + port + "/" + database, username, password);
			stmt = conn.createStatement();
			rs = stmt.executeQuery ("SELECT `key`, `value` FROM `settings` WHERE `key`='music_fileRoot' OR `key`='music_localStartPath' OR `key`='music_webStartPath' or `key`='music_flacTranscodePath' or `key`='music_audio' or `key`='music_video'");
			rs.next();
			
			do {
				String key = rs.getString("key");
				String value = rs.getString("value");
				
				if (key.equals ("music_fileRoot")) {
					fileRoot = value.replace("\\", "/");
				} else if (key.equals ("music_localStartPath")) {
					serverStartPath = value.replace("\\", "/");
				} else if (key.equals ("music_webStartPath")) {
					webStartPath = value;
				} else if (key.equals ("flacTranscodePath")) {
					flacTranscodePath = value.replace ("\\", "/");
				} else if (key.equals ("music_audio")) {
					String audio_delimited = value.replace ("\\", "/");
					
					audio = audio_delimited.split(";");
				} else if (key.equals ("music_video")) {
					String video_delimited = value.replace ("\\", "/");
					
					video = video_delimited.split(";");
				}
				
				rs.next();
			} while (!rs.isAfterLast());
		} catch (Exception e) {
			e.printStackTrace();
		}
		
		if (isVerbose) {
			System.out.println ("Inputs");
			System.out.println ("######");
			System.out.println ("           host: " + host);
			System.out.println ("           port: " + port);
			System.out.println ("       database: " + database);
			System.out.println ("       username: " + username);
			System.out.println ("       password: " + password);
			System.out.println ("       fileRoot: " + fileRoot);
			System.out.println ("serverStartPath: " + serverStartPath);
			System.out.println ("   webStartPath: " + webStartPath);
		}

		File root = new File (serverStartPath + fileRoot);

		if (!isDebug) {
			// init db for scan
			// reset all *_checked columns to 0 (not checked this scan)
			init_db_for_scan ();

			build_file_list (root);
			
			checkForMissingFiles();
		}

		date = new java.util.Date();
		String end_timestamp = (new Timestamp (date.getTime())).toString();
		
		System.out.println ();
		System.out.println ("    Number of files: " + file_count);
		System.out.println ("              Audio: " + audio_count);
		System.out.println ("              Video: " + video_count);
		System.out.println ("                ----");
		System.out.println ("Number of New files: " + new_count);
		System.out.println ("              Audio: " + new_audio_count);
		System.out.println ("              Video: " + new_video_count);
		System.out.println ("                ----");
		System.out.println ("   Number of errors: " + error_count);
		System.out.println ("            Missing: " + missing_file_count);
		System.out.println ("                MD5: " + md5_error_count);
		System.out.println ("                ----");
		System.out.println ("         Start time: " + start_timestamp);
		System.out.println ("           End time: " + end_timestamp);
	}

	private static void showHelp () {
		System.out.println ("Help");
	}
	
	public static void build_file_list (File dir) {
		File[] listing = dir.listFiles();
		String sql_count	= "";
		String sql_insert	= "";
		String sql = "";
		ResultSet rs;
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			Connection conn = null;
			Statement stmt = null;
			conn = DriverManager.getConnection("jdbc:mysql://" + host + ":" + port + "/" + database, username, password);
			stmt = conn.createStatement();
			
			for (int i = 0; i < listing.length; i++) {
				String path_str = listing[i].toString();
				int extention_start_index = path_str.lastIndexOf(".") >= 0 ? path_str.lastIndexOf(".") : 0;
				String extention = path_str.substring (extention_start_index);
				
				if (listing[i].isDirectory()) {
					build_file_list (listing[i]);
				} else if (isExtentionValid (path_str, audio) || isExtentionValid (path_str, video)) {
						FileInputStream fis = new FileInputStream(listing[i]);
						String path = listing[i].toString().replace(serverStartPath, webStartPath).replace("'", "\\'");
				      
						sql_count = "SELECT COUNT(*) FROM `music` WHERE `path` =  '" + path + "';";
						rs = stmt.executeQuery (sql_count);
						rs.next();
						int count = rs.getInt("COUNT(*)");
					      
				      if (count == 1) {
				    	  String md5			= DigestUtils.md5Hex(fis);	

						  System.out.print (isVerbose ? "**EXISTS*" : "E");
				    	  
				    	  sql = "SELECT `MUSIC_ID`, `md5` FROM `music` WHERE `path` =  '" + path + "';";
				    	  rs = stmt.executeQuery (sql);
				    	  rs.next();
				    	  String db_id = rs.getString("MUSIC_ID");
				    	  String db_md5 = rs.getString("md5");
				    	  boolean md5_status = md5.equals(db_md5);
				    	  
				    	  if (md5_status) {
							if (isVerbose) {
								System.out.print ("**PASSED**");
							}
							
							stmt.executeUpdate("UPDATE  `sarah`.`music` SET `last_md5_pass` = CURRENT_TIMESTAMP, `md5_checked` = '1' WHERE `music`.`MUSIC_ID` =" + db_id + ";");
				    	  } else {
							if (isVerbose) {
								System.out.print ("** FAIL **");
							}
							
							stmt.executeUpdate("UPDATE  `sarah`.`music` SET `md5_checked` = '1' WHERE `music`.`MUSIC_ID` =" + db_id + ";");
				    	  	stmt.executeUpdate ("INSERT INTO `sarah`.`error` (`subsystem`, `description`) VALUES ('Music Updater', 'MD5 HASH FAIL:" + path + "; id: " + db_id + "');");
				    	  	
				    	  	error_count++;
				    	  	md5_error_count++;
				    	  }
				    	  
						  if (isVerbose) {
							System.out.println (md5 + "|" + path);
						  }
						  
						  if (isExtentionValid (path_str, audio)) {
							audio_count++;
						  } else if (isExtentionValid (path_str, video)) {
							video_count++;
						  }
					fis.close();
				      } else {
							System.out.print (isVerbose ? "****NEW**** " + path + "\n" : "N");

							String artist		= "";
							String album		= "";
							String title		= "";
							String comment		= "";
							String year			= "";
							String track		= "";
							String disc_no		= "";
							String composer		= "";
							String artist_sort	= "";
							String md5			= "";
							
							if (listing[i].toString().toLowerCase().endsWith(".mp3")) {
								AudioFile f = AudioFileIO.read(listing[i]);
								Tag tag = f.getTag();
								
								artist		= getTagField (tag, FieldKey.ARTIST);
								album		= getTagField (tag, FieldKey.ALBUM);
								title		= getTagField (tag, FieldKey.TITLE);
								comment		= getTagField (tag, FieldKey.COMMENT);
								year		= getTagField (tag, FieldKey.YEAR);
								track		= getTagField (tag, FieldKey.TRACK);
								disc_no		= getTagField (tag, FieldKey.DISC_NO);
								composer	= getTagField (tag, FieldKey.COMPOSER);
								artist_sort	= getTagField (tag, FieldKey.ARTIST_SORT);
							} else if (listing[i].toString().toLowerCase().endsWith(".flac")) {
								AudioFile f = AudioFileIO.read(listing[i]);
								FlacTag tag = (FlacTag)f.getTag();
								VorbisCommentTag vorbisTag = tag.getVorbisCommentTag();
								
								artist		= getFlacTagField (vorbisTag, FieldKey.ARTIST);
								album		= getFlacTagField (vorbisTag, FieldKey.ALBUM);
								title		= getFlacTagField (vorbisTag, FieldKey.TITLE);
								comment		= getFlacTagField (vorbisTag, FieldKey.COMMENT);
								year		= getFlacTagField (vorbisTag, FieldKey.YEAR);
								track		= getFlacTagField (vorbisTag, FieldKey.TRACK);
								disc_no		= getFlacTagField (vorbisTag, FieldKey.DISC_NO);
								composer	= getFlacTagField (vorbisTag, FieldKey.COMPOSER);
								artist_sort	= getFlacTagField (vorbisTag, FieldKey.ARTIST_SORT);
							} else if (listing[i].toString().toLowerCase().endsWith(".m4a")) {
								AudioFile f = AudioFileIO.read (listing[i]);
								Mp4Tag mp4tag = (Mp4Tag)f.getTag();
								
								artist		= getMp4TagField (mp4tag, Mp4FieldKey.ARTIST);
								album		= getMp4TagField (mp4tag, Mp4FieldKey.ALBUM);
								title		= getMp4TagField (mp4tag, Mp4FieldKey.TITLE);
								comment		= getMp4TagField (mp4tag, Mp4FieldKey.COMMENT);
								year		= getMp4TagField (mp4tag, Mp4FieldKey.MM_ORIGINAL_YEAR);
								track		= getMp4TagField (mp4tag, Mp4FieldKey.TRACK);
							}
							
							md5	= DigestUtils.md5Hex(fis);
							
							sql =	"INSERT INTO `sarah`.`music` (`path`, `artist`, `track`, `album`, `track_no`, `year`, `md5`, `last_md5_pass`, `md5_checked`) " +
									"VALUES ('" + path + "', '" + artist + "', '" + title + "', '" + album + "', " + (isInteger(track) ? track : -1) + ", '" + year  + "', '" + md5 + "', CURRENT_TIMESTAMP, '1')";
							
							stmt.executeUpdate(sql);
							
							if (isExtentionValid (path_str, audio)) {
								new_audio_count++;
							} else if (isExtentionValid (path_str, video)) {
								new_video_count++;
							}
							
							new_count++;
				      }
					drive_listing[file_count++] = listing[i];
				}
			}
			
			conn.close();
		} catch (Exception e) {
			sqlError (e, sql);
		}
		
	}
	
	private static boolean isExtentionValid (String path, String[] extentions) {
		for (int i = 0; i <= extentions.length - 1; i++) {
			if (path.toLowerCase().endsWith (extentions[i].toLowerCase())) {
				return true;
			}
		}
		
		return false;
	}
	
	private static void init_db_for_scan () {
		String sql = "";
		ResultSet rs;
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			Connection conn = null;
			Statement stmt = null;
			conn = DriverManager.getConnection("jdbc:mysql://" + host + ":" + port + "/" + database, username, password);
			stmt = conn.createStatement();
			
			stmt.executeUpdate("UPDATE  `sarah`.`music` SET `md5_checked` = 0;");
		} catch (Exception e) {
			sqlError (e, sql);
		}
	}

	// http://stackoverflow.com/questions/5439529/determine-if-a-string-is-an-integer-in-java on 20140420 @ 12:57 EST
	public static boolean isInteger(String s) {
	    try { 
	        Integer.parseInt(s); 
	    } catch(NumberFormatException e) { 
	        return false; 
	    }
	    // only got here if we didn't return false
	    return true;
	}

	private static void checkForMissingFiles () {
		String sql = "";
		ResultSet rs;
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			Connection conn = null;
			Statement stmt = null;
			conn = DriverManager.getConnection("jdbc:mysql://" + host + ":" + port + "/" + database, username, password);
			stmt = conn.createStatement();
			
			sql = "SELECT COUNT(*) FROM `music` WHERE `md5_checked` =  '0';";
			rs = stmt.executeQuery (sql);
			rs.next();
			missing_file_count = rs.getInt ("COUNT(*)");
			
			error_count += missing_file_count;
			
			if (missing_file_count >= 1) {
				sql = "SELECT * FROM `music` WHERE `md5_checked` =  '0'";
				rs = stmt.executeQuery (sql);
				rs.next();
				
				System.out.println ();
				System.out.println ("Missing files:");
				do {
					String path = rs.getString("path").replace(webStartPath, serverStartPath);
					
					File file = new File(path);
					
					System.out.println ("\t" + file.exists() + ":" + path);
					
					rs.next();
				} while (!rs.isAfterLast());
			}
		} catch (Exception e) {
			sqlError (e, sql);
		}
	}

	private static String getTagField (Tag tag, FieldKey key) {
		String field = "";

		try {
			field = tag.getFirst(key).replace("'", "\\'");
		} catch (Exception e) {}

		return field;
	}
	private static String getFlacTagField (VorbisCommentTag tag, FieldKey key) {
		String field = "";

		try {
			field = tag.getFirst(key).replace("'", "\\'");
		} catch (Exception e) {}

		return field;
	}
	private static String getMp4TagField (Mp4Tag tag, Mp4FieldKey key) {
		String field = "";

		try {
			field = tag.getFirst(key).replace("'", "\\'");
		} catch (Exception e) {}

		return field;
	}
	
	private static void sqlError (Exception e) {
		e.printStackTrace();
		System.out.println ("Error: " + e.getMessage());
	}
	private static void sqlError (Exception e, String sql) {
		e.getMessage();
		System.out.println ("Error: " + e.getMessage());
		System.out.println ("  SQL: " + sql);
	}
}
