<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use Mockery\Exception;

/**
 * @OA\Tag(
 *     name="Statistics",
 *     description="API endpoints for platform statistics and analytics"
 * )
 */
class StatisticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/statistics/courses",
     *     tags={"Statistics"},
     *     summary="Get course statistics",
     *     description="Retrieve comprehensive statistics about courses including totals, status breakdown, categories, and enrollment data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Course statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=150, description="Total number of courses"),
     *                 @OA\Property(
     *                     property="by_status",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="status", type="string", example="published"),
     *                         @OA\Property(property="count", type="integer", example=120)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="by_category",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="count", type="integer", example=25),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Web Development")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="most_enrolled",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Laravel Development"),
     *                         @OA\Property(property="status", type="string", example="published"),
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="enrollments_count", type="integer", example=245)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="recent",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="title", type="string", example="Advanced React Concepts"),
     *                         @OA\Property(property="created_at", type="string", format="datetime")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching course statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="error fetching courses")
     *         )
     *     )
     * )
     */
    public function getCourseStats()
    {
        try {
            $totalCourses = Course::count();
            // SELECT status, COUNT(id) FROM courses GROUP BY status;
            $courseByStatus = Course::select('status', \DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            /*
             * SELECT COUNT(courses.id) AS totalCourses, categories.name
                FROM courses
                JOIN categories ON categories.id = courses.category_id
                GROUP BY categories.id;
             */
            $coursesByCategory = Course::select('category_id', \DB::raw('count(*) as count'))
                ->with('category:id,name')
                ->groupBy('category_id')
                ->get();

            /*
             *SELECT courses.title, COUNT(enrollments.id) AS enrollments
                FROM courses
                JOIN enrollments ON enrollments.course_id = courses.id
                GROUP BY courses.id
                ORDER BY enrollments desc;
             */
            $mostEnrolledCourses = Course::withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->limit(5)
                ->get(['id', 'title', 'status', 'category_id']);

            $recentCourses = Course::orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'title', 'created_at']);

            return response()->json([
                'succcess' => true,
                'data' => [
                    'total' => $totalCourses,
                    'by_status' => $courseByStatus,
                    'by_category' => $coursesByCategory,
                    'most_enrolled' => $mostEnrolledCourses,
                    'recent' => $recentCourses,
                ]
            ]);
        } catch (Exception $e) {
            \Log::error("error getting courses stats: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "error fetching courses"
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/statistics/categories",
     *     tags={"Statistics"},
     *     summary="Get category statistics",
     *     description="Retrieve comprehensive statistics about categories including totals, courses per category, and enrollment data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Category statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="totalCategories", type="integer", example=12, description="Total number of categories"),
     *                 @OA\Property(
     *                     property="categoriesByCourse",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Web Development"),
     *                         @OA\Property(property="courses_count", type="integer", example=45)
     *                     ),
     *                     description="Top 10 categories by number of courses"
     *                 ),
     *                 @OA\Property(
     *                     property="categoryByEnrollments",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Mobile Development"),
     *                         @OA\Property(property="enrollments_count", type="integer", example=320)
     *                     ),
     *                     description="Top 5 categories by enrollment count"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching category statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error getting categories stats")
     *         )
     *     )
     * )
     */
    public function getCategoryStats()
    {
        try {
            $totalCategories = Category::count();

            $categoriesByCourses = Category::withCount('courses')
                ->orderByDesc('courses_count')
                ->limit(10)
                ->get(['id', 'name', 'courses_count']);


            $categoriesByEnrollments = \DB::table('categories')
                ->select('categories.id', 'categories.name', \DB::raw('count(enrollments.id) as enrollments_count'))
                ->join('courses', 'courses.category_id', '=', 'categories.id')
                ->join('enrollments', 'enrollments.course_id', '=', 'courses.id')
                ->groupBy('categories.id')
                ->orderByDesc('enrollment_count')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalCategories' => $totalCategories,
                    'categoriesByCourse' => $categoriesByCourses,
                    'categoryByEnrollments' => $categoriesByEnrollments
                ]
            ]);

        } catch (\Exception $e)
        {
            \Log::error("Error getting categories stats: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "message" => "Error getting categories stats"
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/statistics/tags",
     *     tags={"Statistics"},
     *     summary="Get tag statistics",
     *     description="Retrieve comprehensive statistics about tags including usage and popularity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tag statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="totalTags", type="integer", example=50, description="Total number of tags"),
     *                 @OA\Property(
     *                     property="mostUsedTags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="JavaScript"),
     *                         @OA\Property(property="courses_count", type="integer", example=28)
     *                     ),
     *                     description="Top 10 most used tags across courses"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching tag statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error getting tags stats")
     *         )
     *     )
     * )
     */
    public function getTagsStats()
    {
        try {
            $totalTags = Tag::count();

            $mostUsedTags = Tag::withCount('courses')
                ->orderByDesc('courses_count')
                ->limit(20)
                ->get(['id', 'name', 'courses_count']);

            // Get tags with most enrollments
            $tagsByEnrollment = \DB::table('tags')
                ->select('tags.id', 'tags.name', \DB::raw('count(enrollments.id) as enrollment_count'))
                ->join('course_tag', 'tags.id', '=', 'course_tag.tag_id')
                ->join('courses', 'course_tag.course_id', '=', 'courses.id')
                ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->groupBy('tags.id', 'tags.name')
                ->orderByDesc('enrollment_count')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalTags' => $totalTags,
                    'tagsByCourse' => $mostUsedTags,
                    'tagsByEnrollment' => $tagsByEnrollment
                ]
            ]);
        } catch (Exception $e) {
            \Log::error("Error getting tas: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "error fetching tags"
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/statistics/dashboard",
     *     tags={"Statistics"},
     *     summary="Get dashboard overview statistics",
     *     description="Retrieve comprehensive platform overview with key metrics for dashboard",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="totalCourses", type="integer", example=150),
     *                 @OA\Property(property="totalCategories", type="integer", example=12),
     *                 @OA\Property(property="totalTags", type="integer", example=50),
     *                 @OA\Property(property="totalEnrollments", type="integer", example=1250),
     *                 @OA\Property(property="totalUsers", type="integer", example=850),
     *                 @OA\Property(property="totalVideos", type="integer", example=320)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching dashboard statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error getting dashboard stats")
     *         )
     *     )
     * )
     */
    public function getDashboardStats()
    {
        try {
            $totalCourses = Course::count();
            $totalCategories = Category::count();
            $totalTags = Tag::count();
            $totalEnrollments = \DB::table('enrollments')->count();
            $totalUsers = \DB::table('users')->count();
            $totalVideos = \DB::table('videos')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalCourses' => $totalCourses,
                    'totalCategories' => $totalCategories,
                    'totalTags' => $totalTags,
                    'totalEnrollments' => $totalEnrollments,
                    'totalUsers' => $totalUsers,
                    'totalVideos' => $totalVideos,
                ]
            ]);
        } catch (Exception $e) {
            \Log::error("error getting dashboard stats: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "error fetching dashboard stats"
            ]);
        }
    }
}
